<?php

use App\Core\Database;

class ModifyBoostPurchasesNullablePackage
{
    public function up()
    {
        $db = Database::getInstance();
        // Modify package_id to be nullable
        $db->exec("ALTER TABLE boost_purchases MODIFY COLUMN package_id INT NULL");
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->exec("ALTER TABLE boost_purchases MODIFY COLUMN package_id INT NOT NULL");
    }
}
