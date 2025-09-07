# cURL Examples

Minimal, copy-paste friendly cURL requests.  

Set your base URL (change port if needed):
```bash
BASE="http://localhost:8000"
EMAIL="shipper1@example.com" 
PASSWORD="password"  

# Login to get Bearer token
curl -X POST "$BASE/api/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"'"$EMAIL"'","password":"'"$PASSWORD"'"}'


# !! Copy token from response and set it
TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."

# Show all loads (open)
curl -s "$BASE/api/loads?status=open&per_page=20&sort=created_at&order=desc" \
  -H "Authorization: Bearer $TOKEN"


```
