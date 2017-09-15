PROD_SERVER   = "52.52.64.27"
#PROD_SERVER    = "54.85.206.20"   
set  :application_env,  "demo"
set :user, "ubuntu"
set :deploy_to, "/home/ubuntu/applications/#{application}"
#role :app, "server1", "server2", "server3"
role :app,              PROD_SERVER #,PROD_SERVER1
role :web,              PROD_SERVER #,PROD_SERVER1
role :migration,        PROD_SERVER
after "deploy:update", "purge_varnish_cache"