create table products
( sku char(12) not null primary key,
 price float(5,2) not null,
 name char(100) not null,
 color char(50) not null,
 category char(30) not null,
 quantity_available int unsigned not null,
 image char(50) not null
);
create table employee
( ssn char(9) not null primary key,
 password char(50) not null,
 f_name char(20) not null,
 m_init char(1) not null,
 l_name char(20) not null,
 address char(100) not null,
 b_date char(10) not null,
 sex char(1) not null,
 salary int unsigned not null,
 super_ssn char(9) null
);
create table customer
( username char(30) not null primary key,
 password char(50) not null,
 f_name char(20) not null,
 m_init char(1) not null,
 l_name char(20) not null,
 address char(100) not null,
 email char(50) not null
);
create table orders
( ordernum char(9) not null primary key,
 order_date char(10) not null,
 total_cost float(7,2) not null,
 user char(30) not null
);
create table contents
( contents_id int unsigned not null primary key auto_increment,
 order_num char(9) not null,
 sku char(12) not null,
 quantity int unsigned not null,
 cost float(7,2) not null
);