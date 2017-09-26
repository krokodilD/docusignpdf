<?php
/**
 * Created by PhpStorm.
 * User: Daniil Krok
 * Date: 19.09.2017
 */

namespace Daniilkrok\Docusignpdf\Http;

use Daniilkrok\Docusignpdf\Docusign;
use Daniilkrok\Docusignpdf\Fpdm;

use App\Http\Controllers\Controller;

class DocusignpdfController extends Controller
{

    public function index()
    {
        $entityBody = file_get_contents('php://input');
        $requestData = json_decode($entityBody);
        //var_dump($requestData); exit();
        // fields array for PDF template
        $fields = $this->fieldsMapping($requestData->fields_data);
        $pdfOutput = $this->fillPDF($requestData->file->input, $requestData->file->output, $fields);
        $result = $this->sendToDocusign([
            'pdf_file' => $pdfOutput,
            'email_subject' => $requestData->docusign->email_subject,
            'email_message' => $requestData->docusign->email_message,
            'recipient_name' => $requestData->docusign->recipient_name,
            'recipient_email' => $requestData->docusign->recipient_email,
            'send_email' => $requestData->docusign->send_email,
        ]);
        echo $result;
    }

    public function fillPDF($pdfPathInput, $pdfPathOutput, $fields)
    {
        $pdf = new Fpdm\FPDM($pdfPathInput); // always need insert a path to PDF file
        $pdf->processingPDF('pdftk'); // this is the launch of the pdftk plugin for converting PDF to editable PDF for filling fields.
        $pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8

        $pdf->Merge(); // merge fields data with PDF template
        $pdf->Output('F', $pdfPathOutput); // can use parameters like "F" for save adn etc.

        return $pdfPathOutput;
    }

    public function sendToDocusign($data)
    {
        $Docusign = new Docusign\Docusign([
            'host' => env("DK_DUS_HOST"),
            'integrator_key' => env("DK_DUS_INTEGRATOR_KEY"),
            'login' => env("DK_DUS_LOGIN"),
            'password' => env("DK_DUS_PASSWORD"),
        ]);
        $Docusign->setRecipient([
            'email_subject' => $data['email_subject'],
            'email_message' => $data['email_message'],
            'recipient_name' => $data['recipient_name'],
            'recipient_email' => $data['recipient_email'],
        ]);
        $Docusign->setDocumentPath($data['pdf_file']);

        if ($data['send_email'] == 1)
            $Docusign->SendEmail(); // use if you want to send an email to recipient, from DocuSign with link to a file

        $EnvelopeID = $Docusign->createEnvelope();

        return json_encode([
            'envelope_id' => $EnvelopeID
        ]);
    }

    public function fieldsMapping($object)
    {
        $fieldsData = $object->data;
        $template = __DIR__ . '/../pdf_templates/' . $object->mapping_template . '.map';
        $template = file($template);

        $fieldsDataArray = [];
        foreach($template as $key => $line) {
            $lineData = explode('=', trim($line));
            $field = $lineData[0];
            if (strripos($lineData[1], '|') !== false) {
                $valueData = explode('|', trim($lineData[1]));
                $map = str_replace('/', '->', $valueData[0]);
                $option = $valueData[1];

                $value = '';
                if ($option){
                    $optionData = explode('*', trim($option));
                    switch ($optionData[0]) {
                        case 'substr':
                            $value = substr($this->getValueByMap($fieldsData, $map), $optionData[1], $optionData[2]);
                            break;
                        case 'radiobutton':
                            $oValue = $this->getValueByMap($fieldsData, $map);
                            foreach ($optionData as $key => $item) {
                                if ($key == 0) continue;
                                if (strtolower($oValue) == strtolower($item)) {
                                    $radiobuttonKey = $key-1;
                                    $field .= '#'.$radiobuttonKey;
                                    $value = 'M';
                                }
                            }
                            break;
                    }
                }
                $fieldsDataArray[$field] = $value;
            }else{
                $map = str_replace('/', '->', $lineData[1]);
                $fieldsDataArray[$field] = $this->getValueByMap($fieldsData, $map);
            }
        }

        return $fieldsDataArray;
    }

    function getValueByMap($obj, $path_str)
    {
        $val = null;

        $path = preg_split('/->/', $path_str);
        $node = $obj;
        while (($prop = array_shift($path)) !== null) {
            if (!is_object($obj) || !property_exists($node, $prop)) {
                $val = null;
                break;

            }
            $val = $node->$prop;
            // TODO: Insert any logic here for cleaning up $val

            $node = $node->$prop;
        }

        return $val;
    }
}