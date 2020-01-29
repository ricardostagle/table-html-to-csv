# Export table inside an html file to csv file.

Simple code to convert html file to csv reading te html code , getting table values and moving them to an array for finally export the csv file.

This is a code in PHP with a form which uploads the html and export csv file.

We have to modify php.ini settings with the following parameters:

* file_uploads=On
* upload_max_filesize=100M
* memory_limit=512M
* max_execution_time=180
