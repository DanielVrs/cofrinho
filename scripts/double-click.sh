#!/bin/bash
# Simula o clique duplo (Etapa 19): manda o mesmo POST /api/expenses duas vezes ao mesmo tempo.
# Uso: scripts/double-click.sh          -> sem Idempotency-Key (cria dois lançamentos)
#      scripts/double-click.sh --key    -> com a mesma Idempotency-Key nas duas (cria um só)
set -eu

API=${API:-http://localhost:8000}
DIR=$(mktemp -d); JAR="$DIR/jar"
ORIGIN="Origin: http://localhost:5173"
JSON="Content-Type: application/json"
ACCEPT="Accept: application/json"

xsrf() { grep XSRF-TOKEN "$JAR" | awk '{print $7}' | python3 -c 'import sys,urllib.parse;print(urllib.parse.unquote(sys.stdin.read().strip()))'; }

# login por cookie, como o React faz
curl -s -c "$JAR" -o /dev/null -H "$ORIGIN" "$API/sanctum/csrf-cookie"
curl -s -b "$JAR" -c "$JAR" -o /dev/null -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $(xsrf)" \
  -d '{"email":"demo@cofrinho.test","password":"password"}' "$API/api/login"
TOKEN=$(xsrf)

CATEGORY=$(curl -s -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" "$API/api/categories" | python3 -c 'import sys,json;print(json.load(sys.stdin)["data"][0]["id"])')
TODAY=$(date +%F)
DESC="Clique duplo $(date +%T) $RANDOM"
BODY="{\"description\":\"$DESC\",\"amount_cents\":1000,\"spent_on\":\"$TODAY\",\"category_id\":$CATEGORY}"

KEY_HEADER="X-Nada: 1"   # cabeçalho inofensivo quando não há chave
if [ "${1:-}" = "--key" ]; then
  KEY=$(python3 -c 'import uuid;print(uuid.uuid4())')
  KEY_HEADER="Idempotency-Key: $KEY"
  echo "Mesma Idempotency-Key nas duas requisições: $KEY"
else
  echo "Sem Idempotency-Key"
fi

export DIR JAR ORIGIN ACCEPT JSON TOKEN API BODY KEY_HEADER
seq 2 | xargs -P 2 -I{} sh -c \
  'curl -s -D "$DIR/h{}" -o "$DIR/b{}" -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $TOKEN" -H "$KEY_HEADER" -d "$BODY" "$API/api/expenses"'

for i in 1 2; do
  STATUS=$(head -1 "$DIR/h$i" | awk '{print $2}')
  REPLAYED=$(grep -i '^idempotent-replayed' "$DIR/h$i" | tr -d '\r' | awk '{print $2}')
  ID=$(python3 -c 'import sys,json;print(json.load(open(sys.argv[1]))["data"]["id"])' "$DIR/b$i" 2>/dev/null || echo "?")
  echo "  requisição $i: status $STATUS, lançamento id $ID${REPLAYED:+, Idempotent-Replayed: $REPLAYED}"
done

# conta no banco quantos lançamentos nasceram com essa descrição
curl -s -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" "$API/api/expenses?month=${TODAY%-*}" > "$DIR/list"
python3 - "$DIR/list" "$DESC" <<'PY'
import sys, json
items = [e for e in json.load(open(sys.argv[1]))["data"] if e["description"] == sys.argv[2]]
print(f"Lançamentos criados no banco: {len(items)} (ids {', '.join(str(e['id']) for e in items)})")
PY
rm -rf "$DIR"
