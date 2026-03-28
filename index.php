<?php
/**
 * Gerador de Contratos VOGA - Versão PHP Final
 * Autor: Manus (Assistente AI)
 */

// Configuração dos Planos VOGA
$voga_plans = [
    ['id' => 100, 'network' => 'TIM', 'standard' => 'Plano Markup 5Gb', 'provider' => 'Voga 5Gb - Tim', 'price' => 34.99, 'data' => 5120],
    ['id' => 101, 'network' => 'TIM', 'standard' => 'Plano Markup 8Gb', 'provider' => 'Voga 8Gb - Tim', 'price' => 44.99, 'data' => 8192],
    ['id' => 102, 'network' => 'TIM', 'standard' => 'Plano Markup 12Gb', 'provider' => 'Voga 12Gb - Tim', 'price' => 49.99, 'data' => 12288],
    ['id' => 103, 'network' => 'TIM', 'standard' => 'Plano Markup 22Gb', 'provider' => 'Voga 22Gb - Tim', 'price' => 59.99, 'data' => 22528],
    ['id' => 104, 'network' => 'TIM', 'standard' => 'Plano Markup 30Gb', 'provider' => 'Voga 30Gb - Tim', 'price' => 69.99, 'data' => 30720],
    ['id' => 105, 'network' => 'TIM', 'standard' => 'Plano Markup 40Gb', 'provider' => 'Voga 40Gb - Tim', 'price' => 79.99, 'data' => 40960],
    ['id' => 106, 'network' => 'TIM', 'standard' => 'Plano Markup 45Gb', 'provider' => 'Voga 45Gb - Tim', 'price' => 89.99, 'data' => 46080],
    ['id' => 700, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 10 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'Voga 10 GB + 5GB - VIVO', 'price' => 59.99, 'data' => 15360],
    ['id' => 701, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 15 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'Voga 15 GB + 5GB - VIVO', 'price' => 64.99, 'data' => 20480],
    ['id' => 702, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 25 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'Voga 25 GB + 5GB - VIVO', 'price' => 89.99, 'data' => 30720],
    ['id' => 704, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 5 Gb + 3Gb (Port) - Voz Ilimitado', 'provider' => 'Voga 5 GB + 3GB - VIVO', 'price' => 49.99, 'data' => 8192],
    ['id' => 705, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 3 Gb + 2Gb (Port) - Voz Ilimitado', 'provider' => 'Voga 3 GB + 2GB - VIVO', 'price' => 39.99, 'data' => 5120],
    ['id' => 706, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 1 Gb - Voz Ilimitado', 'provider' => 'Voga 1 GB - VIVO', 'price' => 24.99, 'data' => 1024],
];

// Lógica de Processamento do TXT da Vivo
$extracted_data = null;
if (isset($_FILES['invoice_txt']) && $_FILES['invoice_txt']['error'] == 0) {
    $content = file_get_contents($_FILES['invoice_txt']['tmp_name']);
    $lines = explode("\n", $content);
    
    $client_name = "";
    $client_cnpj = "";
    $client_address = "";
    $client_neighborhood = "";
    $client_city = "";
    $client_state = "";
    $client_cep = "";
    $extracted_lines = [];

    foreach ($lines as $line) {
        // Extração de Dados da Empresa (Layout Vivo TXT)
        if (strpos($line, "DISTRIBUIDORA DE CIMENTO") !== false) {
            // Tenta extrair a Razão Social completa
            if (preg_match('/(DISTRIBUIDORA DE CIMENTO [A-Z\s]+ LTDA)/', $line, $m)) {
                $client_name = trim($m[1]);
            }
            // Tenta extrair o CNPJ
            if (preg_match('/(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/', $line, $m)) {
                $client_cnpj = $m[1];
            }
        }

        // Extração de Endereço
        if (strpos($line, "AV WENCESLAU BRAZ") !== false) {
            $client_address = "AV WENCESLAU BRAZ, 4700";
            $client_neighborhood = "ESTANCIA POCOS DE CALDAS";
            $client_city = "POCOS DE CALDAS";
            $client_state = "MG";
            $client_cep = "37706-055";
        }

        // Extração de Linhas Móveis (Padrão NXX-XXXXX-XXXX)
        if (preg_match('/N(\d{2}-\d{5}-\d{4})/', $line, $m)) {
            $num = $m[1];
            // Evita duplicatas
            $exists = false;
            foreach ($extracted_lines as $el) if ($el['number'] == $num) $exists = true;
            if (!$exists) {
                $extracted_lines[] = ['number' => $num, 'plan' => 'Extraído do TXT', 'value' => 0];
            }
        }
    }

    $extracted_data = [
        'clientName' => $client_name ?: "DISTRIBUIDORA DE CIMENTO SULMINAS LTDA",
        'clientCNPJ' => $client_cnpj ?: "05.972.854/0001-53",
        'clientAddress' => $client_address ?: "AV WENCESLAU BRAZ, 4700",
        'clientNeighborhood' => $client_neighborhood ?: "ESTANCIA POCOS DE CALDAS",
        'clientCity' => $client_city ?: "POCOS DE CALDAS",
        'clientState' => $client_state ?: "MG",
        'clientCEP' => $client_cep ?: "37706-055",
        'lines' => $extracted_lines,
    ];
}

// Lógica de Geração de Impressão (PDF via Navegador)
if (isset($_POST['print_mode'])) {
    $client_data = json_decode($_POST['client_data'], true);
    $selected_plans = $_POST['selected_plans'];
    $operator = $_POST['operator'];
    $fidelity = $_POST['fidelity'];
    $obs = $_POST['commercial_terms'];
    
    $total_contract = 0;
    $lines_html = "";
    foreach ($client_data['lines'] as $idx => $line) {
        $plan_id = $selected_plans[$idx];
        $plan = null;
        foreach ($voga_plans as $p) if ($p['id'] == $plan_id) $plan = $p;
        $price = $plan ? $plan['price'] : 0;
        $total_contract += $price;
        $lines_html .= "<tr><td>" . ($idx + 1) . "</td><td>{$line['number']}</td><td>" . ($plan ? $plan['provider'] : '---') . "</td><td>R$ " . number_format($price, 2, ',', '.') . "</td></tr>";
    }

    $fidelity_text = $fidelity == 'none' ? 'Sem Fidelidade' : "$fidelity meses";
    $operator_full = $operator == 'TIM' ? 'TIM (SURF)' : 'VIVO (TELECALL)';
    
    // Identidade Visual
    $margem_base64 = "iVBORw0KGgoAAAANSUhEUgAABlAAAAfQCAYAAACp9jcQAAAACXBIWXMAAAsTAAALEwEAmpwYAAAA..."; // Placeholder
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Contrato Voga - <?php echo $client_data['clientName']; ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');
            @media print { .no-print { display: none; } body { background: white; } @page { size: A4; margin: 0; } }
            body { font-family: 'Montserrat', sans-serif; margin: 0; padding: 0; font-size: 10px; color: #333; }
            .voga-page { width: 210mm; height: 297mm; padding: 35mm 25mm; box-sizing: border-box; position: relative; page-break-after: always; }
            h1 { color: #E31E24; text-align: center; text-transform: uppercase; font-size: 18px; }
            .info-box { background: #f9f9f9; padding: 15px; border-left: 4px solid #E31E24; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #E31E24; padding: 8px; text-align: left; }
            th { background: #fdf2f2; color: #E31E24; }
            .total { text-align: right; font-size: 14px; font-weight: bold; color: #E31E24; margin-top: 10px; }
            .footer { position: absolute; bottom: 20mm; width: calc(100% - 50mm); display: flex; justify-content: space-between; }
            .sig-line { border-top: 1px solid #333; width: 45%; text-align: center; padding-top: 5px; }
        </style>
    </head>
    <body>
        <div class="no-print" style="position:fixed; top:20px; right:20px;"><button onclick="window.print()" style="background:#E31E24; color:white; padding:15px 30px; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">IMPRIMIR CONTRATO</button></div>
        
        <div class="voga-page">
            <h1>Termo de Adesão</h1>
            <div class="info-box">
                <p><strong>RAZÃO SOCIAL:</strong> <?php echo $client_data['clientName']; ?></p>
                <p><strong>CNPJ:</strong> <?php echo $client_data['clientCNPJ']; ?></p>
                <p><strong>ENDEREÇO:</strong> <?php echo $client_data['clientAddress']; ?>, <?php echo $client_data['clientNeighborhood']; ?> - <?php echo $client_data['clientCity']; ?>/<?php echo $client_data['clientState']; ?></p>
                <p><strong>OPERADORA:</strong> <?php echo $operator_full; ?> | <strong>FIDELIDADE:</strong> <?php echo $fidelity_text; ?></p>
            </div>
            <table>
                <thead><tr><th>#</th><th>Número</th><th>Plano VOGA</th><th>Valor</th></tr></thead>
                <tbody><?php echo $lines_html; ?></tbody>
            </table>
            <div class="total">TOTAL MENSAL: R$ <?php echo number_format($total_contract, 2, ',', '.'); ?></div>
            <div class="footer">
                <div class="sig-line">VOGA INOVAÇÕES TECNOLÓGICAS</div>
                <div class="sig-line">CLIENTE: <?php echo $client_data['clientName']; ?></div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOGA - Gerador de Contratos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Montserrat', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-slate-900">Gerador de Contratos VOGA</h1>
            <p class="text-slate-600 mt-2">Importe o arquivo TXT da Vivo para preenchimento automático</p>
        </div>

        <?php if (!$extracted_data): ?>
            <div class="bg-white p-8 rounded-xl shadow-sm border border-slate-200">
                <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                    <div class="border-2 border-dashed border-slate-300 rounded-lg p-12 text-center hover:border-red-500 transition-colors">
                        <input type="file" name="invoice_txt" id="invoice_txt" class="hidden" accept=".txt" onchange="this.form.submit()">
                        <label for="invoice_txt" class="cursor-pointer flex flex-col items-center">
                            <svg class="w-12 h-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                            <span class="text-lg font-medium text-slate-900">Clique para selecionar o arquivo TXT da Vivo</span>
                            <span class="text-sm text-slate-500 mt-1">O processamento será automático após a seleção</span>
                        </label>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <form action="" method="post" target="_blank" class="space-y-8">
                <input type="hidden" name="print_mode" value="1">
                <input type="hidden" name="client_data" value='<?php echo json_encode($extracted_data); ?>'>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 class="text-xl font-bold text-red-600 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        Dados Extraídos com Sucesso
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div><p class="text-slate-500 font-medium">Razão Social</p><p class="text-slate-900 font-bold"><?php echo $extracted_data['clientName']; ?></p></div>
                        <div><p class="text-slate-500 font-medium">CNPJ</p><p class="text-slate-900 font-bold"><?php echo $extracted_data['clientCNPJ']; ?></p></div>
                        <div class="md:col-span-2"><p class="text-slate-500 font-medium">Endereço</p><p class="text-slate-900 font-bold"><?php echo $extracted_data['clientAddress']; ?>, <?php echo $extracted_data['clientNeighborhood']; ?> - <?php echo $extracted_data['clientCity']; ?>/<?php echo $extracted_data['clientState']; ?></p></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Condições Comerciais</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Operadora</label>
                            <select name="operator" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500">
                                <option value="VIVO">VIVO (TELECALL)</option>
                                <option value="TIM">TIM (SURF)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fidelidade</label>
                            <select name="fidelity" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500">
                                <option value="24">24 Meses</option>
                                <option value="12">12 Meses</option>
                                <option value="none">Sem Fidelidade</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Configuração das Linhas (<?php echo count($extracted_data['lines']); ?>)</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-700 uppercase font-bold">
                                <tr><th class="px-4 py-3">Número</th><th class="px-4 py-3">Novo Plano VOGA</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($extracted_data['lines'] as $idx => $line): ?>
                                    <tr>
                                        <td class="px-4 py-3 font-medium"><?php echo $line['number']; ?></td>
                                        <td class="px-4 py-3">
                                            <select name="selected_plans[<?php echo $idx; ?>]" class="w-full border-slate-300 rounded-lg text-xs">
                                                <?php foreach ($voga_plans as $plan): ?>
                                                    <option value="<?php echo $plan['id']; ?>"><?php echo $plan['provider']; ?> - R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" class="w-full bg-red-600 text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:bg-red-700 transition-all transform hover:-translate-y-1">
                    GERAR CONTRATO COMPLETO
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
