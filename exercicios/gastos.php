<?php

declare(strict_types=1);

// ---------------------------------------------------------------
// Parte 1: cinco gastos em arrays associativos.
// O valor fica em centavos (inteiro) para evitar erro de
// arredondamento com float. R$ 12,50 vira 1250.
// ---------------------------------------------------------------

$gastos = [
    ['descricao' => 'Almoço',          'categoria' => 'Alimentação', 'valor' => 3250],
    ['descricao' => 'Ônibus',          'categoria' => 'Transporte',  'valor' => 450],
    ['descricao' => 'Mercado',         'categoria' => 'Alimentação', 'valor' => 18790],
    ['descricao' => 'Cinema',          'categoria' => 'Lazer',       'valor' => 3200],
    ['descricao' => 'Uber',            'categoria' => 'Transporte',  'valor' => 1890],
];

// Converte centavos para o formato brasileiro: 1250 -> "R$ 12,50".
function formatarReais(int $centavos): string
{
    return 'R$ ' . number_format($centavos / 100, 2, ',', '.');
}

// ---------------------------------------------------------------
// Parte 2: total por categoria usando arrays.
// Recebe a lista de gastos e devolve ['Alimentação' => 22040, ...].
// ---------------------------------------------------------------

function totalPorCategoria(array $gastos): array
{
    $totais = [];

    foreach ($gastos as $gasto) {
        $categoria = $gasto['categoria'];

        if (! isset($totais[$categoria])) {
            $totais[$categoria] = 0;
        }

        $totais[$categoria] += $gasto['valor'];
    }

    return $totais;
}

// ---------------------------------------------------------------
// Parte 3: a classe Gasto.
// Propriedades tipadas e declaradas no construtor (constructor
// property promotion). readonly: depois de criado, não muda.
// ---------------------------------------------------------------

class Gasto
{
    public function __construct(
        public readonly string $descricao,
        public readonly string $categoria,
        public readonly int $valor,
    ) {
    }

    public function valorFormatado(): string
    {
        return formatarReais($this->valor);
    }
}

// ---------------------------------------------------------------
// Parte 4: o mesmo total, agora com objetos Gasto.
// A diferença está no acesso: $gasto->valor em vez de $gasto['valor'].
// ---------------------------------------------------------------

/** @param Gasto[] $gastos */
function totalPorCategoriaComObjetos(array $gastos): array
{
    $totais = [];

    foreach ($gastos as $gasto) {
        $totais[$gasto->categoria] = ($totais[$gasto->categoria] ?? 0) + $gasto->valor;
    }

    return $totais;
}

// Transforma cada array em um objeto Gasto.
$objetos = array_map(
    fn (array $g) => new Gasto($g['descricao'], $g['categoria'], $g['valor']),
    $gastos,
);

// ---------------------------------------------------------------
// Saída
// ---------------------------------------------------------------

echo "Gastos:\n";
foreach ($objetos as $gasto) {
    echo "  {$gasto->descricao} ({$gasto->categoria}): {$gasto->valorFormatado()}\n";
}

echo "\nTotal por categoria (arrays):\n";
foreach (totalPorCategoria($gastos) as $categoria => $total) {
    echo "  {$categoria}: " . formatarReais($total) . "\n";
}

echo "\nTotal por categoria (objetos):\n";
foreach (totalPorCategoriaComObjetos($objetos) as $categoria => $total) {
    echo "  {$categoria}: " . formatarReais($total) . "\n";
}
