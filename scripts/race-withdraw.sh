#!/bin/bash
# Provoca a race condition do saque (Etapa 18).
# Cria um cofre com R$ 100,00 e dispara 10 saques de R$ 80,00 ao mesmo tempo.
# Uso: scripts/race-withdraw.sh [quantidade de saques, padrão 10]
set -eu

API=${API:-http://localhost:8000}
N=${1:-10}
JAR=$(mktemp)
ORIGIN="Origin: http://localhost:5173"
JSON="Content-Type: application/json"
ACCEPT="Accept: application/json"

xsrf() { grep XSRF-TOKEN "$JAR" | awk '{print $7}' | python3 -c 'import sys,urllib.parse;print(urllib.parse.unquote(sys.stdin.read().strip()))'; }

# login por cookie, como o React faz
curl -s -c "$JAR" -o /dev/null -H "$ORIGIN" "$API/sanctum/csrf-cookie"
curl -s -b "$JAR" -c "$JAR" -o /dev/null -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $(xsrf)" \
  -d '{"email":"demo@cofrinho.test","password":"password"}' "$API/api/login"
TOKEN=$(xsrf)

# cofre novo com R$ 100,00
VAULT=$(curl -s -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $TOKEN" \
  -d '{"name":"Teste de corrida"}' "$API/api/vaults" | python3 -c 'import sys,json;print(json.load(sys.stdin)["data"]["id"])')
curl -s -b "$JAR" -o /dev/null -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $TOKEN" \
  -d '{"amount_cents":10000}' "$API/api/vaults/$VAULT/deposit"

echo "Cofre $VAULT com R\$ 100,00. Disparando $N saques de R\$ 80,00 ao mesmo tempo..."
export JAR ORIGIN ACCEPT JSON TOKEN API VAULT
seq "$N" | xargs -P "$N" -I{} sh -c \
  'curl -s -o /dev/null -w "%{http_code}\n" -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" -H "$JSON" -H "X-XSRF-TOKEN: $TOKEN" -d "{\"amount_cents\":8000}" "$API/api/vaults/$VAULT/withdraw"' \
  | sort | uniq -c | sed 's/^ *\([0-9]*\) \(.*\)/  \1 resposta(s) \2/'

curl -s -b "$JAR" -H "$ORIGIN" -H "$ACCEPT" "$API/api/vaults/$VAULT" > "$JAR.json"
python3 - "$JAR.json" <<'PY'
import sys, json
d = json.load(open(sys.argv[1]))["data"]
print(f"Saldo final: R$ {d['balance_cents']/100:.2f}")
print(f"Soma das movimentações: R$ {d['movements_total_cents']/100:.2f}")
print(f"Saiu do cofre: R$ {(10000 - d['movements_total_cents'])/100:.2f}")
PY
rm -f "$JAR" "$JAR.json"
