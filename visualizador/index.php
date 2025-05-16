<?php
function formatMoney($value) {
  return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function formatChave($chave) {
  return trim(chunk_split($chave, 4, ' '));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['xml_file'])) {
  $tmp = $_FILES['xml_file']['tmp_name'];
  if (file_exists($tmp)) {
    $xml = simplexml_load_file($tmp);

    $infNFe = $xml->NFe->infNFe;
    $emit = $infNFe->emit;
    $dest = $infNFe->dest;
    $items = $infNFe->det;
    $total = $infNFe->total->ICMSTot;
    $ide = $infNFe->ide;
    $infAdic = $infNFe->infAdic;

    $chave = str_replace("NFe", "", (string)$infNFe["Id"]);
    $chaveFormatada = formatChave($chave);
  }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>DANFE Comercial</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-size: 12px; }
    .box { border: 1px solid #000; padding: 10px; margin-bottom: 10px; }
    .titulo { font-weight: bold; text-transform: uppercase; background: #eee; padding: 5px; margin-bottom: 5px; }
    .linha { border-top: 1px solid #ccc; margin: 5px 0; }
    .print-btn { position: fixed; top: 10px; right: 10px; z-index: 999; }
    .nf-table th, .nf-table td { font-size: 11px; padding: 4px; }
    svg#barcode { margin-top: 10px; }
  </style>
</head>
<body class="container">

  <h2 class="my-3">DANFE - Documento Auxiliar da Nota Fiscal Eletrônica</h2>

  <form method="POST" enctype="multipart/form-data" class="mb-4">
    <input type="file" name="xml_file" accept=".xml" class="form-control mb-2" required>
    <button type="submit" class="btn btn-primary">Visualizar</button>
  </form>

  <?php if (isset($emit)): ?>
    <button onclick="window.print()" class="btn btn-secondary print-btn d-print-none">🖨️ Imprimir</button>

    <div class="box text-center">
      <div class="titulo">CHAVE DE ACESSO</div>
      <div style="font-size: 18px; letter-spacing: 2px;"><?= $chaveFormatada ?></div>
      <svg id="barcode"></svg>
    </div>

    <div class="box">
      <div class="titulo">Emitente</div>
      <p><strong><?= $emit->xNome ?></strong><br>
        <?= $emit->enderEmit->xLgr ?>, <?= $emit->enderEmit->nro ?> - <?= $emit->enderEmit->xBairro ?><br>
        <?= $emit->enderEmit->xMun ?> - <?= $emit->enderEmit->UF ?> - CEP: <?= $emit->enderEmit->CEP ?><br>
        CNPJ: <?= $emit->CNPJ ?> - IE: <?= $emit->IE ?>
      </p>
    </div>

    <div class="box">
      <div class="titulo">Destinatário</div>
      <p><strong><?= $dest->xNome ?></strong><br>
        <?= $dest->enderDest->xLgr ?>, <?= $dest->enderDest->nro ?> - <?= $dest->enderDest->xBairro ?><br>
        <?= $dest->enderDest->xMun ?> - <?= $dest->enderDest->UF ?> - CEP: <?= $dest->enderDest->CEP ?><br>
        CNPJ/CPF: <?= $dest->CNPJ ?? $dest->CPF ?> - IE: <?= $dest->IE ?>
      </p>
    </div>

    <div class="box">
      <div class="titulo">Produtos / Serviços</div>
      <table class="table table-bordered table-sm nf-table">
        <thead class="table-light">
          <tr>
            <th>Código</th><th>Descrição</th><th>NCM</th><th>CFOP</th><th>UN</th>
            <th>Qtd</th><th>V. Unit</th><th>Desc</th><th>V. Total</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= $item->prod->cProd ?></td>
            <td><?= $item->prod->xProd ?></td>
            <td><?= $item->prod->NCM ?></td>
            <td><?= $item->prod->CFOP ?></td>
            <td><?= $item->prod->uCom ?></td>
            <td><?= $item->prod->qCom ?></td>
            <td><?= formatMoney($item->prod->vUnCom) ?></td>
            <td><?= formatMoney($item->prod->vDesc ?? 0) ?></td>
            <td><?= formatMoney($item->prod->vProd) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="box">
      <div class="titulo">Totais</div>
      <p>
        Base ICMS: <?= formatMoney($total->vBC) ?> |
        ICMS: <?= formatMoney($total->vICMS) ?> |
        Frete: <?= formatMoney($total->vFrete) ?> |
        Desconto: <?= formatMoney($total->vDesc) ?><br>
        IPI: <?= formatMoney($total->vIPI) ?> |
        Total Produtos: <?= formatMoney($total->vProd) ?> |
        <strong>Valor Total NF: <?= formatMoney($total->vNF) ?></strong>
      </p>
    </div>

    <div class="box">
      <div class="titulo">Informações Adicionais</div>
      <p><?= $infAdic->infCpl ?? '---' ?></p>
    </div>
  <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <?php if (isset($chave)): ?>
  <script>
    JsBarcode("#barcode", "<?= $chave ?>", {
      format: "CODE128",
      lineColor: "#000",
      width: 2,
      height: 60,
      displayValue: false
    });
  </script>
  <?php endif; ?>

</body>
</html>
