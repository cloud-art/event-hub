CREATE TABLE IF NOT EXISTS events (
    id text PRIMARY KEY,
    published boolean NOT NULL,
    content jsonb NOT NULL
);
