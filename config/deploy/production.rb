#PROD_SERVER     = "54.183.37.5"
PROD_SERVER1  = "52.90.24.196"
PROD_SERVER  = "34.225.103.77"
PROD_SERVER2  = "34.193.81.169"
set  :application_env,  "production"
set :user, "ubuntu"
set :deploy_to, "/home/ubuntu/applications/#{application}"
role :app,              PROD_SERVER ,PROD_SERVER1 ,PROD_SERVER2
role :web,              PROD_SERVER ,PROD_SERVER1 ,PROD_SERVER2
role :migration,        PROD_SERVER 
#after "deploy:update", "purge_varnish_cache"