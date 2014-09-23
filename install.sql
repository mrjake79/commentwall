create table comment (
    id bigint not null primary key auto_increment,
    name varchar (256) not null, 
    email varchar (256), 
    website varchar (256), 
    comment text not null, 
    submitted datetime not null default now()
);
