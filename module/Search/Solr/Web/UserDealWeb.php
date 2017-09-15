<?php

namespace Solr\Web;

use Solr\Mob\UserDealMob;

class UserDealWeb {

    public function getUserDeals($origReq) {
        $udm = new UserDealMob();
        return $udm->getUserDeals($origReq);
    }

}
