# INSTALLATION SCRIPT
apt-get update
apt-get install libapache2-mod-geoip
# EDIT /etc/apache2/mods-available/geoip.conf
#
# <IfModule mod_geoip.c>
#     GeoIPEnable On
#     GeoIPDBFile /usr/share/GeoIP/GeoIP.dat
# </IfModule>
cd /tmp
wget -q http://geolite.maxmind.com/download/geoip/database/GeoIP.dat.gz

if [ -f GeoIP.dat.gz ]
then
    gzip -d GeoIP.dat.gz
    sudo rm -f /usr/share/GeoIP/GeoIP.dat
   sudo mv -f GeoIP.dat /usr/share/GeoIP/GeoIP.dat
else
    echo "The GeoIP library could not be downloaded and updated"
fi