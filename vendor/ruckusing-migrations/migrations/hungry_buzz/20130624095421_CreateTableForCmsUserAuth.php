<?php

class CreateTableForCmsUserAuth extends Ruckusing_Migration_Base
{
    public function up()
    {

    	/*$this->execute("create table cms_AuthItem
		(
		   name varchar(64) not null,
		   type integer not null,
		   description text,
		   bizrule text,
		   data text,
		   primary key (name)
		);");

		$this->execute("create table cms_AuthItemChild
		(
		   parent varchar(64) not null,
		   child varchar(64) not null,
		   primary key (parent,child),
		   foreign key (parent) references cms_AuthItem (name) on delete cascade on update cascade,
		   foreign key (child) references cms_AuthItem (name) on delete cascade on update cascade
		);");

		$this->execute("create table cms_AuthAssignment
		(
		   itemname varchar(64) not null,
		   userid varchar(64) not null,
		   bizrule text,
		   data text,
		   primary key (itemname,userid),
		   foreign key (itemname) references cms_AuthItem (name) on delete cascade on update cascade
		);");

		$this->execute("create table cms_Rights
		(
			itemname varchar(64) not null,
			type integer not null,
			weight integer not null,
			primary key (itemname),
			foreign key (itemname) references cms_AuthItem (name) on delete cascade on update cascade
		);");*/

    }//up()

    public function down()
    {
    	/*$this->execute("drop table if exists cms_AuthItem;");
    	$this->execute("drop table if exists cms_AuthItemChild;");
    	$this->execute("drop table if exists cms_AuthAssignment;");
    	$this->execute("drop table if exists cms_Rights;");*/
    }//down()
}
