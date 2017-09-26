<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 24.09.2017
 * Time: 16:52
 */

namespace Daniilkrok\Docusignpdf\Docusign;

use Mail;

class Docusign
{
    const LOG_PATH = __DIR__."/logs/error.log";

    var $host; // host for DocuSign sandbox or production. SANDBOX: https://demo.docusign.net
    var $integratorKey; // integrator key from DocuSign Admin -> API and Keys
    var $login; // your login on DocuSign
    var $password; // you password on DocuSign
    var $send_email = false; // send email to recipient or not. Default = false

    var $recipient_email; // required if you send a email to user from API
    var $recipient_name; // required if you send a email to user from API
    var $email_subject; // required
    var $email_message; // required

    var $document_path; // path to input PDF file

    function __construct($param)
    {
        $this->host = $param['host'];
        $this->integratorKey = $param['integrator_key'];
        $this->login = $param['login'];
        $this->password = $param['password'];
    }

    public function setRecipient($data)
    {
        $this->recipient_email = $data['recipient_email']?$data['recipient_email']:'';
        $this->recipient_name = $data['recipient_name']?$data['recipient_name']:'';
        $this->email_subject = $data['email_subject'];
        $this->email_message = $data['email_message'];
    }

    /**
     * @param mixed $document_path
     */
    public function setDocumentPath($document_path)
    {
        $this->document_path = $document_path;
    }

    public function SendEmail()
    {
        $this->send_email = true;
    }

    public function createEnvelope()
    {
        // Input your info here:
        $url = $this->host . "/restapi/v2/login_information";

        $document_name = basename($this->document_path);

        // construct the authentication header:
        $header = "<DocuSignCredentials><Username>" . $this->login . "</Username><Password>" . $this->password . "</Password><IntegratorKey>" . $this->integratorKey . "</IntegratorKey></DocuSignCredentials>";

        /////////////////////////////////////////////////////////////////////////////////////////////////
        // STEP 1 - Login (to retrieve baseUrl and accountId)
        /////////////////////////////////////////////////////////////////////////////////////////////////
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-DocuSign-Authentication: $header"));

        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ( $status != 200 ) {
            $error = "HTTP Status Code: " . $status. "\n".$json_response;;
            $this->Error($error);
        }

        $response = json_decode($json_response, true);
        //$accountId = $response["loginAccounts"][0]["accountId"];
        $baseUrl = $response["loginAccounts"][0]["baseUrl"];
        curl_close($curl);

        //--- display results
        //echo "\naccountId = " . $accountId . "\nbaseUrl = " . $baseUrl . "\n";

        /////////////////////////////////////////////////////////////////////////////////////////////////
        // STEP 2 - Create an envelope with one recipient, one tab, one document and send!
        /////////////////////////////////////////////////////////////////////////////////////////////////
        if ($this->send_email) {
            $data = "{
              \"emailBlurb\":\"" . $this->email_message . "\",
              \"emailSubject\":\"" . $this->email_subject . "\",
              \"documents\":[
                {
                  \"documentId\":\"1\",
                  \"name\":\"" . $document_name . "\"
                }
              ],
              \"recipients\":{
                \"signers\":[
                  {
                    \"email\":\"$this->recipient_email\",
                    \"name\":\"$this->recipient_name\",
                    \"recipientId\":\"1\",
                    \"tabs\":{
                      \"signHereTabs\":[
                        {
                          \"documentId\":\"1\",
                          \"pageNumber\":\"1\"
                        }
                      ]
                    }
                  }
                ]
              },
              \"status\":\"sent\"
            }";
        }else{
            $data = "{
              \"emailBlurb\":\"" . $this->email_message . "\",
              \"emailSubject\":\"" . $this->email_subject . "\",
              \"documents\":[
                {
                  \"documentId\":\"1\",
                  \"name\":\"".$document_name."\"
                }
              ],
              \"status\":\"created\"
            }";
        }

        $file_contents = file_get_contents($this->document_path);

        $requestBody = "\r\n"
            ."\r\n"
            ."--myboundary\r\n"
            ."Content-Type: application/json\r\n"
            ."Content-Disposition: form-data\r\n"
            ."\r\n"
            ."$data\r\n"
            ."--myboundary\r\n"
            ."Content-Type:application/pdf\r\n"
            ."Content-Disposition: file; filename=\"order_form.pdf\"; documentid=1 \r\n"
            ."\r\n"
            ."$file_contents\r\n"
            ."--myboundary--\r\n"
            ."\r\n";

        // *** append "/envelopes" to baseUrl and as signature request endpoint
        $curl = curl_init($baseUrl . "/envelopes" );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data;boundary=myboundary',
                'Content-Length: ' . strlen($requestBody),
                "X-DocuSign-Authentication: $header" )
        );

        $json_response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ( $status != 201 ) {
            $error = "HTTP Status Code: " . $status . "\n".$json_response;
            $this->Error($error);
        }

        $response = json_decode($json_response, true);
        $envelopeId = $response["envelopeId"];

        //--- display results
        //echo "Document is sent! Envelope ID = " . $envelopeId . "\n\n";
        return $envelopeId;
    }

    function Error($msg) {
        //--------------------
        error_log("(".date('d/m/Y H:i:s').") ".$msg."\n", 3, self::LOG_PATH);
        // send email
        //TODO: better email delivery
        mail(env("DK_DUS_ADMIN_EMAIL"), 'error on query', $msg);
        die($msg);
        //die('<b>FPDF-Merge Error:</b> '.$msg);
    }

}