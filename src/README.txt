1) Inser into .env file settings (set your data):

DK_DUS_HOST=https://demo.docusign.net
DK_DUS_INTEGRATOR_KEY=*******-****-****-****-*********
DK_DUS_LOGIN=*******
DK_DUS_PASSWORD=********
DK_DUS_ADMIN_EMAIL=*********

2) Create request and send JSON data like:

{
    "file":{
        "input":"PATH TO FILE",
        "output":"PATH TO FUTURE FILE"
    },
    "docusign":{
        "email_subject":"SUBJECT",
        "email_message":"MESSAGE",
        "recipient_name":"NAME USER",
        "recipient_email":"EMAIL",
        "send_email":"0"
    },
    "fields_data":{
    "mapping_template":"TEMPLATE NAME FILE",
    "data":{JSON DATA FOR FIELDS}
    }
}

