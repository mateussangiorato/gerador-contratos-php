<?php
/**
 * Gerador de Contratos VOGA - Versão PHP Final (Identidade Visual ODT)
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

// Lógica de Processamento do TXT
$extracted_data = null;
if (isset($_FILES['invoice_txt']) && $_FILES['invoice_txt']['error'] == 0) {
    $content = file_get_contents($_FILES['invoice_txt']['tmp_name']);
    $lines = explode("\n", $content);
    
    $client_name = "";
    $client_cnpj = "";
    $extracted_lines = [];

    foreach ($lines as $line) {
        if (strpos($line, "DISTRIBUIDORA") !== false || strpos($line, "LTDA") !== false) {
            if (empty($client_name)) $client_name = trim($line);
            if (preg_match('/(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/', $line, $m)) $client_cnpj = $m[1];
        }
        if (preg_match('/N(\d{2}-\d{5}-\d{4})/', $line, $m)) {
            $extracted_lines[] = ['number' => $m[1], 'plan' => 'Extraído do TXT', 'value' => 0];
        }
    }

    $extracted_data = [
        'clientName' => $client_name ?: "Cliente Exemplo LTDA",
        'clientCNPJ' => $client_cnpj ?: "00.000.000/0001-00",
        'clientAddress' => "Rua Exemplo, 123",
        'clientNeighborhood' => "Centro",
        'clientCity' => "Poços de Caldas",
        'clientState' => "MG",
        'clientCEP' => "37701-000",
        'lines' => $extracted_lines,
    ];
}

// Se for uma requisição de geração de PDF (via print)
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
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Contrato Voga - <?php echo $client_data['clientName']; ?></title>
        <style>
            @media print { .no-print { display: none; } body { background: white; padding: 0; } .page-break { page-break-before: always; } }
            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.5; color: #333; font-size: 11px; margin: 0; padding: 0; }
            
            /* CAPA VERMELHA */
            .cover-page { 
                background-color: #e11d48; 
                height: 100vh; 
                display: flex; 
                flex-direction: column; 
                justify-content: center; 
                align-items: center; 
                color: white; 
                text-align: center;
                position: relative;
            }
            .cover-logo { font-size: 80px; font-family: 'Brush Script MT', cursive; margin-bottom: 20px; }
            .cover-year { position: absolute; bottom: 50px; font-size: 18px; }

            /* BORDAS VOGA */
            .voga-border {
                position: relative;
                padding: 60px;
                min-height: 100vh;
                box-sizing: border-box;
            }
            .voga-border::before {
                content: "";
                position: absolute;
                top: 20px; left: 20px; right: 20px; bottom: 20px;
                border: 1px solid #e11d48;
                border-radius: 15px;
                pointer-events: none;
            }
            .corner-logo {
                position: absolute;
                width: 60px;
                opacity: 0.3;
            }
            .top-left { top: 30px; left: 30px; }
            .bottom-right { bottom: 30px; right: 30px; }

            .content-wrapper { max-width: 700px; margin: auto; position: relative; z-index: 1; }
            
            h1 { color: #e11d48; font-size: 18px; text-align: center; text-transform: uppercase; margin-bottom: 30px; }
            h2 { color: #e11d48; font-size: 14px; margin-top: 25px; border-bottom: 1px solid #e11d48; padding-bottom: 5px; }
            
            .data-grid { display: grid; grid-cols: 2; gap: 10px; margin-bottom: 20px; }
            .data-item { margin-bottom: 8px; }
            .label { font-weight: bold; color: #e11d48; }

            table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
            th { background-color: #fdf2f2; color: #e11d48; border: 1px solid #e11d48; padding: 6px; }
            td { border: 1px solid #e11d48; padding: 6px; }

            .total-box { 
                margin-top: 20px; 
                background: #fdf2f2; 
                padding: 15px; 
                border-radius: 8px; 
                border: 1px solid #e11d48;
                text-align: right;
            }
            .total-value { font-size: 18px; font-weight: bold; color: #e11d48; }

            .sig-area { margin-top: 60px; display: flex; justify-content: space-between; text-align: center; }
            .sig-line { border-top: 1px solid #333; width: 45%; padding-top: 5px; font-size: 9px; }

            .page-number { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); background: white; border: 1px solid #e11d48; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; color: #e11d48; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 1000;">
            <button onclick="window.print()" style="background: #e11d48; color: white; border: none; padding: 12px 25px; border-radius: 30px; cursor: pointer; font-weight: bold; shadow: 0 4px 6px rgba(0,0,0,0.1);">GERAR PDF FINAL</button>
        </div>

        <!-- PÁGINA 1: CAPA -->
        <div class="cover-page">
            <div class="cover-logo">voga</div>
            <div class="cover-year"><?php echo date('2026'); ?></div>
        </div>

        <!-- PÁGINA 2: PROPOSTA -->
        <div class="page-break voga-border">
            <div class="content-wrapper">
                <h1>Proposta Comercial e Termo de Adesão – Voga</h1>
                
                <h2>Dados da Empresa</h2>
                <div style="margin-top:15px;">
                    <p><span class="label">Nome Empresarial:</span> <?php echo $client_data['clientName']; ?></p>
                    <p><span class="label">CNPJ:</span> <?php echo $client_data['clientCNPJ']; ?></p>
                    <p><span class="label">Endereço:</span> <?php echo $client_data['clientAddress']; ?></p>
                    <p><span class="label">Bairro:</span> <?php echo $client_data['clientNeighborhood']; ?> | <span class="label">Cidade:</span> <?php echo $client_data['clientCity']; ?> | <span class="label">Estado:</span> <?php echo $client_data['clientState']; ?> | <span class="label">CEP:</span> <?php echo $client_data['clientCEP']; ?></p>
                    <p><span class="label">Operadora:</span> <?php echo $operator_full; ?></p>
                </div>

                <h2>Linhas para Portabilidades</h2>
                <p><span class="label">Quantidade:</span> <?php echo count($client_data['lines']); ?></p>

                <h2>Linhas e Planos</h2>
                <table>
                    <thead><tr><th>#</th><th>Número</th><th>Plano</th><th>Valor Unit.</th></tr></thead>
                    <tbody><?php echo $lines_html; ?></tbody>
                </table>

                <div class="total-box">
                    <p style="margin:0; font-size:10px; color:#666;">Valor Total Mensal do Contrato</p>
                    <p class="total-value">R$ <?php echo number_format($total_contract, 2, ',', '.'); ?></p>
                </div>

                <div style="margin-top:20px;">
                    <p><span class="label">Observações:</span> <?php echo $obs ?: 'Nenhuma'; ?></p>
                    <p><span class="label">Fidelidade:</span> <?php echo $fidelity_text; ?></p>
                </div>
            </div>
            <div class="page-number">2</div>
        </div>

        <!-- PÁGINA 3: CONTRATO -->
        <div class="page-break voga-border">
            <div class="content-wrapper">
                <h1>Contrato de Prestação de Serviços de Telefonia Móvel – Voga</h1>
                <p><strong>CONTRATANTE:</strong> VOGA INOVAÇÕES TECNOLÓGICA LTDA</p>
                <p><strong>CLIENTE:</strong> <?php echo $client_data['clientName']; ?></p>
                
                <p style="text-align: justify; margin-top: 15px;">Este contrato explica como funcionam os serviços da Voga e estabelece seus direitos e deveres como CLIENTE. Nosso compromisso é ser claro, transparente e eficiente.</p>
                
                <h2>1. Objeto</h2>
                <p>1.1. Este contrato trata da prestação de serviços de telefonia móvel (SMP), que podem incluir: Ligações (voz); Internet móvel (dados); Mensagens de texto (SMS); Tudo conforme o plano escolhido no Termo de Adesão.</p>
                <p>1.2. A Voga atua como gestora comercial e integradora do serviço, utilizando infraestrutura de operadoras autorizadas pela ANATEL (<?php echo $operator_full; ?>), conforme definido no Termo de Adesão.</p>
                
                <h2>2. Início, Duração e Ativação</h2>
                <p>2.1. O serviço inicia após assinatura do Termo de Adesão e ativação da linha. 2.2. O contrato é por prazo indeterminado. 2.6. Fidelidade de <?php echo $fidelity_text; ?> conforme adesão.</p>
                
                <h2>8. Cobrança e Pagamento</h2>
                <p>8.1. Modelo pós-pago, com faturamento mensal. 8.2. Em caso de atraso, multa de 2% e juros de 1% ao mês.</p>
                
                <h2>9. Fidelidade e Multa Rescisória</h2>
                <p>9.2. Em caso de cancelamento antecipado pelo CLIENTE, sem justa causa, será cobrada multa rescisória de 30% sobre o valor das parcelas vincendas.</p>

                <div class="sig-area">
                    <div class="sig-line">VOGA INOVAÇÕES TECNOLÓGICAS LTDA</div>
                    <div class="sig-line"><?php echo $client_data['clientName']; ?></div>
                </div>
            </div>
            <div class="page-number">3</div>
        </div>
        <script>window.onload = function() { if(!window.location.search.includes('noprint')) window.print(); }</script>
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
    <title>Gerador VOGA PHP - Final</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Gerador VOGA</h1>
                <p class="text-slate-600">Versão Final - Identidade Visual ODT</p>
            </div>
            <div class="bg-rose-600 text-white px-4 py-2 rounded-lg font-bold">VOGA</div>
        </header>

        <?php if (!$extracted_data): ?>
        <div class="bg-white p-12 rounded-2xl shadow-xl border border-slate-200 text-center">
            <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
                <div class="border-4 border-dashed border-slate-200 rounded-2xl p-16 hover:border-rose-400 transition-all cursor-pointer group" onclick="document.getElementById('fileInput').click()">
                    <div class="text-slate-300 group-hover:text-rose-500 transition-colors mb-4">
                        <svg class="w-20 h-20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <p class="text-xl text-slate-600 font-semibold">Carregar Fatura TXT</p>
                    <p class="text-slate-400 mt-2">Arraste o arquivo ou clique para selecionar</p>
                    <input type="file" name="invoice_txt" id="fileInput" class="hidden" accept=".txt" onchange="this.form.submit()">
                </div>
            </form>
        </div>
        <?php else: ?>
        <form action="" method="post" target="_blank">
            <input type="hidden" name="print_mode" value="1">
            <input type="hidden" name="client_data" value='<?php echo json_encode($extracted_data); ?>'>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-6 bg-rose-600 rounded-full"></span> Dados do Cliente
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase">Nome Empresarial</label>
                                <input type="text" name="dummy_name" value="<?php echo $extracted_data['clientName']; ?>" class="w-full border-none p-0 font-bold text-slate-900 focus:ring-0">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-400 uppercase">CNPJ</label>
                                <input type="text" name="dummy_cnpj" value="<?php echo $extracted_data['clientCNPJ']; ?>" class="w-full border-none p-0 font-bold text-slate-900 focus:ring-0">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
                        <h3 class="font-bold text-slate-900">Configurações</h3>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Operadora</label>
                            <select name="operator" id="operatorSelect" class="w-full border-slate-200 rounded-xl text-sm p-3 bg-slate-50" onchange="filterPlans()">
                                <option value="TIM">TIM (via SURF)</option>
                                <option value="VIVO">VIVO (via TELECALL)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Fidelidade</label>
                            <select name="fidelity" class="w-full border-slate-200 rounded-xl text-sm p-3 bg-slate-50">
                                <option value="none">Sem Fidelidade</option>
                                <option value="12">12 Meses</option>
                                <option value="24">24 Meses</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Observações</label>
                            <textarea name="commercial_terms" class="w-full border-slate-200 rounded-xl text-sm p-3 bg-slate-50" rows="3" placeholder="Isenções, taxas, etc..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-rose-600 text-white py-4 rounded-2xl font-bold hover:bg-rose-700 shadow-lg shadow-rose-200 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            GERAR PDF FORMATADO
                        </button>
                        <a href="index.php" class="block text-center text-sm text-slate-400 hover:text-rose-600 transition-colors font-medium">Cancelar e Voltar</a>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="p-5 text-xs font-bold text-slate-400 uppercase">Linha</th>
                                    <th class="p-5 text-xs font-bold text-slate-400 uppercase">Novo Plano VOGA</th>
                                    <th class="p-5 text-xs font-bold text-slate-400 uppercase text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($extracted_data['lines'] as $idx => $line): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-5 text-sm font-bold text-slate-900"><?php echo $line['number']; ?></td>
                                    <td class="p-5">
                                        <select name="selected_plans[<?php echo $idx; ?>]" class="plan-select w-full border-slate-100 rounded-lg text-xs p-2 focus:ring-rose-500" onchange="updateTotal()">
                                            <option value="">Selecione o plano...</option>
                                            <?php foreach ($voga_plans as $plan): ?>
                                            <option value="<?php echo $plan['id']; ?>" data-network="<?php echo $plan['network']; ?>" data-price="<?php echo $plan['price']; ?>">
                                                <?php echo $plan['provider']; ?> (R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-5 text-sm font-black text-rose-600 text-right line-price">R$ 0,00</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-rose-50">
                                <tr>
                                    <td colspan="2" class="p-6 text-sm font-bold text-rose-900 text-right uppercase">Total do Contrato:</td>
                                    <td id="grandTotal" class="p-6 text-2xl font-black text-rose-900 text-right">R$ 0,00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function filterPlans() {
                const network = document.getElementById('operatorSelect').value;
                document.querySelectorAll('.plan-select').forEach(select => {
                    Array.from(select.options).forEach(opt => {
                        if (opt.value === "") return;
                        opt.style.display = opt.getAttribute('data-network') === network ? 'block' : 'none';
                    });
                    const selected = select.options[select.selectedIndex];
                    if (selected && selected.value !== "" && selected.style.display === 'none') select.value = "";
                });
                updateTotal();
            }

            function updateTotal() {
                let total = 0;
                document.querySelectorAll('.plan-select').forEach(select => {
                    const opt = select.options[select.selectedIndex];
                    const price = opt && opt.value !== "" ? parseFloat(opt.getAttribute('data-price')) : 0;
                    total += price;
                    select.closest('tr').querySelector('.line-price').innerText = 'R$ ' + price.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                });
                document.getElementById('grandTotal').innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            }
            window.onload = filterPlans;
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
