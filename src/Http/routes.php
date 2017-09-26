<?php
/**
 * Created by PhpStorm.
 * User: Daniil Krok
 * Date: 19.09.2017
 */

/*Route::get('dk/docusign/requesttest', function () {
    $data = [
        'file' => [
            'input' => base_path('ICC.pdf'),
            'output' => base_path('zzz2.pdf'),
        ],
        'docusign' => [
            'email_subject' => 'DocuSign API - Please Sign This Document...',
            'email_message' => 'This comes from PHP',
            'recipient_name' => 'Some User',
            'recipient_email' => 'krokodild@gmail.com',
            'send_email' => '0',
        ],
        'fields_data' => [
            'mapping_template' => 'template1',
            'data' => ''//json_decode(file_get_contents(base_path('all-v9.json')))
        ]
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

    // Optional Authentication:
    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    //curl_setopt($curl, CURLOPT_USERPWD, "username:password");
    curl_setopt($curl, CURLOPT_URL, 'http://jheapp.loc/dk/docusign/pdf');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    echo "<pre>";
    var_dump($result);
    exit();
});*/

Route::post('dk/docusign/pdf', 'Daniilkrok\Docusignpdf\Http\DocusignpdfController@index');
