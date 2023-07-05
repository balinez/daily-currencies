CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;

$$ LANGUAGE plpgsql;
create table if not exists currencies
(
    id varchar(8) UNIQUE not null,
    num_code int not null,
    char_code varchar(8)  not null,
    nominal int  not null,
    name text  not null,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE TRIGGER set_timestamp
BEFORE UPDATE ON currencies
FOR EACH ROW
EXECUTE PROCEDURE trigger_set_timestamp();
create table if not exists prices
(
    id serial PRIMARY KEY,
    currencн_id varchar(8) not null,
    value decimal not null,
    actual_date varchar(20) not null,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
CREATE TRIGGER set_timestamp
BEFORE UPDATE ON prices
FOR EACH ROW
EXECUTE PROCEDURE trigger_set_timestamp();