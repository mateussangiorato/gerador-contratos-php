<?php
/**
 * Gerador de Contratos VOGA - Versão PHP Simplificada
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
        $lines_html .= "<tr><td style='border:1px solid #000; padding:4px;'>" . ($idx + 1) . "</td><td style='border:1px solid #000; padding:4px;'>{$line['number']}</td><td style='border:1px solid #000; padding:4px;'>" . ($plan ? $plan['provider'] : '---') . "</td><td style='border:1px solid #000; padding:4px;'>R$ " . number_format($price, 2, ',', '.') . "</td></tr>";
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
            body { font-family: Arial, sans-serif; line-height: 1.3; color: #000; font-size: 11px; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: auto; background: white; padding: 40px; }
            h1 { font-size: 16px; text-align: center; text-decoration: underline; margin-bottom: 20px; }
            h2 { font-size: 14px; text-align: center; text-decoration: underline; margin-top: 30px; margin-bottom: 15px; }
            .section-title { font-weight: bold; text-decoration: underline; margin-top: 15px; margin-bottom: 5px; display: block; }
            .data-box { border: 1px solid #000; padding: 10px; margin-bottom: 15px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #000; padding: 5px; text-align: left; }
            .footer-sig { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
            .sig-line { border-top: 1px solid #000; width: 45%; padding-top: 5px; }
            .red-text { color: #e11d48; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 100;">
            <button onclick="window.print()" style="background: #e11d48; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">SALVAR COMO PDF</button>
        </div>

        <div class="container">
            <!-- PÁGINA 1 -->
            <h1>PROPOSTA COMERCIAL E TERMO DE ADESÃO – Voga</h1>
            
            <div class="section-title">DADOS DA EMPRESA</div>
            <div class="data-box">
                <strong>Nome Empresarial:</strong> <?php echo $client_data['clientName']; ?><br>
                <strong>CNPJ:</strong> <?php echo $client_data['clientCNPJ']; ?><br>
                <strong>Endereço:</strong> <?php echo $client_data['clientAddress']; ?><br>
                <strong>Bairro:</strong> <?php echo $client_data['clientNeighborhood']; ?> | <strong>Cidade:</strong> <?php echo $client_data['clientCity']; ?> | <strong>Estado:</strong> <?php echo $client_data['clientState']; ?> | <strong>CEP:</strong> <?php echo $client_data['clientCEP']; ?><br>
                <strong>Operadora:</strong> <?php echo $operator_full; ?>
            </div>

            <div class="section-title">LINHAS PARA PORTABILIDADES:</div>
            <p><strong>Quantidade:</strong> <?php echo count($client_data['lines']); ?></p>

            <div class="section-title">LINHAS E PLANOS:</div>
            <table>
                <thead><tr style="background:#f3f4f6;"><th>#</th><th>Número</th><th>Plano</th><th>Valor</th></tr></thead>
                <tbody><?php echo $lines_html; ?></tbody>
            </table>

            <div style="margin-top: 15px;">
                <p><strong>Observações:</strong> <?php echo $obs ?: '---'; ?></p>
                <p><strong>Fidelidade:</strong> <?php echo $fidelity_text; ?></p>
                <p style="font-size: 14px; font-weight: bold; margin-top: 10px;">VALOR TOTAL DO CONTRATO: R$ <?php echo number_format($total_contract, 2, ',', '.'); ?></p>
            </div>

            <!-- PÁGINA 2 -->
            <div class="page-break">
                <h2>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE TELEFONIA MÓVEL – Voga</h2>
                <p><strong>CONTRATANTE:</strong> VOGA INOVAÇÕES TECNOLÓGICA LTDA</p>
                <p><strong>CLIENTE:</strong> <?php echo $client_data['clientName']; ?></p>
                
                <p style="margin-top: 15px; text-align: justify;">Este contrato explica como funcionam os serviços da Voga e estabelece seus direitos e deveres como CLIENTE. Nosso compromisso é ser claro, transparente e eficiente.</p>
                
                <div class="section-title">1. OBJETO</div>
                <p>1.1. Este contrato trata da prestação de serviços de telefonia móvel (SMP), que podem incluir: Ligações (voz); Internet móvel (dados); Mensagens de texto (SMS); Tudo conforme o plano escolhido no Termo de Adesão.</p>
                <p>1.2. A Voga atua como gestora comercial e integradora do serviço, utilizando infraestrutura de operadoras autorizadas pela ANATEL (<?php echo $operator_full; ?>), conforme definido no Termo de Adesão.</p>
                
                <div class="section-title">2. INÍCIO, DURAÇÃO E ATIVAÇÃO</div>
                <p>2.1. O serviço inicia após assinatura do Termo de Adesão e ativação da linha. 2.2. O contrato é por prazo indeterminado. 2.6. Fidelidade de <?php echo $fidelity_text; ?> conforme adesão.</p>
                
                <div class="section-title">8. COBRANÇA E PAGAMENTO</div>
                <p>8.1. Modelo pós-pago, com faturamento mensal. 8.2. Em caso de atraso, multa de 2% e juros de 1% ao mês.</p>
                
                <div class="section-title">9. FIDELIDADE E MULTA RESCISÓRIA</div>
                <p>9.2. Em caso de cancelamento antecipado pelo CLIENTE, sem justa causa, será cobrada multa rescisória de 30% sobre o valor das parcelas vincendas.</p>

                <div class="footer-sig">
                    <div class="sig-line">VOGA INOVAÇÕES TECNOLÓGICAS LTDA</div>
                    <div class="sig-line"><?php echo $client_data['clientName']; ?></div>
                </div>
            </div>
            
            <!-- PÁGINA 3 EM DIANTE (RESUMO DAS CLÁUSULAS) -->
            <div class="page-break">
                <div class="section-title">DADOS DA EMPRESA PRESTADORA DE ORIGEM</div>
                <div class="data-box" style="font-size: 9px;">
                    <?php if($operator == 'TIM'): ?>
                    <strong>Nome Empresarial:</strong> SURF TELECOM | <strong>CNPJ:</strong> 10.455.746/0004-96<br>
                    <strong>Endereço:</strong> AV. MAGALHÃES DE CASTRO, n°. 4800, CONJ 161. CIDADE JARDIM, SÃO PAULO/SP.
                    <?php else: ?>
                    <strong>Nome Empresarial:</strong> TELECALL | <strong>CNPJ:</strong> 07.625.852/0001-13<br>
                    <strong>Endereço:</strong> Avenida das Américas, 4.485, Loja 112 e 113, Barra da Tijuca, Rio de Janeiro/RJ.
                    <?php endif; ?>
                </div>
                
                <div class="section-title">ANEXO I – SLA DETALHADO</div>
                <table>
                    <tr style="background:#f3f4f6;"><th>Severidade</th><th>Definição</th><th>Resposta</th><th>Solução</th></tr>
                    <tr><td>Alta</td><td>Indisponibilidade total</td><td>Até 1h</td><td>Até 9h</td></tr>
                    <tr><td>Média</td><td>Degradação parcial</td><td>Até 4h</td><td>Até 36h</td></tr>
                    <tr><td>Baixa</td><td>Impacto leve</td><td>Até 48h</td><td>Até 120h</td></tr>
                </table>
            </div>
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
    <title>Gerador VOGA PHP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Gerador de Contratos VOGA</h1>
            <p class="text-slate-600">Versão PHP - Gerador de PDF Formatado</p>
        </header>

        <?php if (!$extracted_data): ?>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-center">
            <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-12 hover:border-blue-400 transition-colors cursor-pointer" onclick="document.getElementById('fileInput').click()">
                    <div class="text-slate-400 mb-4">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <p class="text-slate-600 font-medium">Arraste a fatura TXT aqui ou clique para selecionar</p>
                    <input type="file" name="invoice_txt" id="fileInput" class="hidden" accept=".txt" onchange="this.form.submit()">
                </div>
            </form>
        </div>
        <?php else: ?>
        <form action="" method="post" target="_blank">
            <input type="hidden" name="print_mode" value="1">
            <input type="hidden" name="client_data" value='<?php echo json_encode($extracted_data); ?>'>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-2 h-6 bg-rose-600 rounded-full"></span> Dados do Cliente
                        </h3>
                        <div class="space-y-3 text-sm">
                            <p><span class="text-slate-500 font-bold">Nome:</span><br><input type="text" name="dummy_name" value="<?php echo $extracted_data['clientName']; ?>" class="w-full border-none p-0 font-bold focus:ring-0"></p>
                            <p><span class="text-slate-500 font-bold">CNPJ:</span><br><input type="text" name="dummy_cnpj" value="<?php echo $extracted_data['clientCNPJ']; ?>" class="w-full border-none p-0 font-bold focus:ring-0"></p>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4">
                        <h3 class="font-bold text-slate-900">Configurações do Contrato</h3>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Operadora de Destino</label>
                            <select name="operator" id="operatorSelect" class="w-full border-slate-300 rounded-lg text-sm" onchange="filterPlans()">
                                <option value="TIM">TIM (via SURF)</option>
                                <option value="VIVO">VIVO (via TELECALL)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fidelidade</label>
                            <select name="fidelity" class="w-full border-slate-300 rounded-lg text-sm">
                                <option value="none">Sem Fidelidade</option>
                                <option value="12">12 Meses</option>
                                <option value="24">24 Meses</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Observações</label>
                            <textarea name="commercial_terms" class="w-full border-slate-300 rounded-lg text-sm" rows="2" placeholder="Ex: Isenção de taxa de ativação..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-rose-600 text-white py-4 rounded-xl font-bold hover:bg-rose-700 shadow-lg transition-all flex items-center justify-center gap-2">
                            GERAR CONTRATO PDF
                        </button>
                        <a href="index.php" class="block text-center text-sm text-slate-500 hover:underline">Limpar e carregar outro</a>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Linha</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Plano VOGA</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-right">Mensalidade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($extracted_data['lines'] as $idx => $line): ?>
                                <tr>
                                    <td class="p-4 text-sm font-medium text-slate-900"><?php echo $line['number']; ?></td>
                                    <td class="p-4">
                                        <select name="selected_plans[<?php echo $idx; ?>]" class="plan-select w-full border-slate-200 rounded-lg text-xs" onchange="updateTotal()">
                                            <option value="">Selecione o plano...</option>
                                            <?php foreach ($voga_plans as $plan): ?>
                                            <option value="<?php echo $plan['id']; ?>" data-network="<?php echo $plan['network']; ?>" data-price="<?php echo $plan['price']; ?>">
                                                <?php echo $plan['provider']; ?> (R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-4 text-sm font-bold text-rose-600 text-right line-price">R$ 0,00</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-rose-50">
                                <tr>
                                    <td colspan="2" class="p-4 text-sm font-bold text-rose-900 text-right uppercase">Total Mensal:</td>
                                    <td id="grandTotal" class="p-4 text-lg font-black text-rose-900 text-right">R$ 0,00</td>
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
