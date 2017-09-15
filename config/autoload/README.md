About this directory:
=====================

By default, this application is configured to load all configs in
`./config/autoload/{,*.}{global,local}.php`. Doing this provides a
location for a developer to drop in configuration override files provided by
modules, as well as cleanly provide individual, application-wide config files
for things like database connections, etc.

Updates:

11-Mar-2016 
Added 'logging' => array('enabled' => TRUE, 'log_to_file' => TRUE, 'log_to_db' => TRUE),
after line 6 in config.php.dist. Please update the same in respective environment.

31-Mar-2017
Added - 'default_graph_version' => 'v2.3'
After line no. 12 in config.php.dist. Please update the same in respective environment.

20 Apr 2017
Added : 'clevertap'=>array(
          'apiurl'=>'https://api.clevertap.com',
          'X-CleverTap-Account-Id'=>'TEST-944-R78-884Z',
          'X-CleverTap-Passcode'=>'QAA-IMW-CIAL'          
        ),

After live 140 in config.php.dist. Please update the same in respective environment in config file at line 184

25 May 2017
Update config.php as per environment
####################################################
After line no. 4 add 'bucket'=> 'local',
After line no. 5 replace 'imagehost' => 's3.amazonaws.com/',
After line no. 146 change 'web_url'=>'munchado-local.com'(As per environment)

