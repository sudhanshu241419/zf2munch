<?php

class InsertDataPromotion extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("INSERT INTO `promotions` (`promotionId`, `promotionName`, `promotionDesc`, `promotionImage`, `promotionSponsorByID`, `promotionPoints`, `promotionStartDate`, `promotionEndDate`, `promotionStatus`, `promotionDisplayOrder`, `promotionTypeId`, `promotionCategoryId`, `promotionCategoryAmount`, `minimumOrder`, `promotionCreatedOn`, `promotionUpdatedOn`) VALUES
(1, 'opening night', 'Reach 600 points and unlock $15 worth of....anythings you want!', '', 0, 1500, '2015-10-04 00:00:00', '2015-10-12 00:00:00', 1, 1, 1, 3, 737436.00, 0.00, '2015-10-04 00:00:00', '2015-10-04 00:00:00'),
(2, '$15 cash back', 'Reach 600 points and unlock $15 worth of....anythings you want!', '', 0, 10, '2015-10-04 00:00:00', '2015-10-12 00:00:00', 1, 2, 2, 1, 15.00, 0.00, '2015-10-04 00:00:00', '2015-10-04 00:00:00'),
(3, 'hispanic night', 'Reach 600 points and unlock $15 worth of....anythings you want!', '', 0, 1500, '2015-10-04 00:00:00', '2015-10-12 00:00:00', 1, 3, 3, 3, 737436.00, 0.00, '2015-10-04 00:00:00', '2015-10-04 00:00:00')");
    }//up()

    public function down()
    {
    }//down()
}
