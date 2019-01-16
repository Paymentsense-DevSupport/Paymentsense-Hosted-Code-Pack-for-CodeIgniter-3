Paymentsense Hosted Code Pack for CodeIgniter 3
===============================================


The Paymentsense Hosted Code Pack for CodeIgniter 3 demonstrates the submission of initial transactions (SALE and PREAUTH) to the Paymentsense gateway by a CodeIgniter 3 application. The supported result delivery methods by this code pack are POST and SERVER.


Installation
-----------------------------------------------

1. Download the Paymentsense Hosted Code Pack for CodeIgniter 3 from https://github.com/Paymentsense-DevSupport/Paymentsense-Hosted-Code-Pack-for-CodeIgniter-3/releases 
2. Unzip/unpack the compressed file and upload the content to a web-accessible directory on your server


Configuration
-----------------------------------------------

1. Set the constants MMS_MERCHANT_ID, MMS_PASSWORD, MMS_HASH_METHOD and MMS_PRE_SHARED_KEY in the application/config/config.php file as per their respective values in your MMS
2. Grant the web user write permission to the directory application/tmp (setfacl -m u:username:rwx application/tmp) or alternatively change the directory mode (chmod 777 application/tmp)


Support
-----------------------------------------------

[devsupport@paymentsense.com](mailto:devsupport@paymentsense.com)
