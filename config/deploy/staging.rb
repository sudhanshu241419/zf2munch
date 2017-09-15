PROD_SERVER     = "staging.hungrybuzz.info"
set  :application_env,  "staging"
set :user, "deploy"
set :deploy_to, "/home/deploy/applications/#{application}"
role :app,              PROD_SERVER
role :web,              PROD_SERVER 
role :migration,        PROD_SERVER
#after "deploy:update", "purge_varnish_cache"
