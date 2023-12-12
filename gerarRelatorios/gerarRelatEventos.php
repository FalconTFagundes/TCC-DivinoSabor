<?php
// Carregar o Composer
require '../vendor/autoload.php';

// Incluir conexao com BD
include_once '../config/constantes.php';
include_once '../config/conexao.php';
include_once '../func/dashboard.php';

// Referenciar o namespace Dompdf
use Dompdf\Dompdf;

try {
    // Chama a função conectar e atribui o resultado à variável $conn
    $conn = conectar();

    // Se a conexão for bem-sucedida, continue com o restante do código
    if ($conn) {
        // QUERY para recuperar os registros do banco de dados
        $query_eventos = "SELECT id, title, color, start, end FROM events";
        // Prepara a QUERY
        $stmt = $conn->prepare($query_eventos);

        // Verificar se a preparação da query foi bem-sucedida
        if (!$stmt) {
            throw new Exception("Falha ao preparar a consulta.");
        }

        // Executar a QUERY
        $stmt->execute();

        // Informacoes para o PDF
        $dados = "<!DOCTYPE html>";
        $dados .= "<html lang='pt-br'>";
        $dados .= "<head>";
        $dados .= "<meta charset='UTF-8'>";
        $dados .= "<style>
            body {
                font-family: 'Arial', sans-serif;
                color: #333;
                margin: 20px;
            }

            h1 {
                color: #9E77F1;
                text-align: center;
                border-bottom: 2px solid #333;
                padding-bottom: 10px;
                margin-bottom: 20px;
            }

            h4 {
                color: #333;
                margin-top: 10px;
            }

            b {
                color: #333;
            }

            hr {
                border-top: 1px solid;
                margin-top: 20px;
                margin-bottom: 30px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            th, td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }

            th {
                background-color: #f2f2f2; /* Cor de fundo mais clara para cabeçalho da tabela */
            }

            td {
                background-color: #fff; /* Cor de fundo branca para células de dados */
            }
        </style>";

        $dados .= "<title>Relatório de Eventos</title>";
        $dados .= "</head>";
        $dados .= "<body>";
        $dados .= "<h1>RELATÓRIO DE EVENTOS</h1>";
        $dados .= "<h4>Usuário Emissor: <b>" . $_SESSION['nomeUser'] . "</b></h4>";

        // Ler os registros retornados do BD
        while ($row_eventos = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row_eventos);

            $dados .= "<table>";
            $dados .= "<tr><th>Código do Evento</th><td>$id</td></tr>";
            $dados .= "<tr><th>Título do Evento</th><td>$title</td></tr>";
            if ($color == "#00BD3f") {
                $dados .= "<tr><th>Cor Destacada no Calendário</th><td style='color: $color;'>Verde</td></tr>";
            } elseif ($color == "#D4C200") {
                $dados .= "<tr><th>Cor Destacada no Calendário</th><td style='color: $color;'>Amarelo</td></tr>";
            } else if ($color == "#9E77F1"){
                $dados .= "<tr><th>Cor Destacada no Calendário</th><td style='color: $color;'>Roxo</td></tr>";
            } else if ($color == "#297BFF"){
                $dados .= "<tr><th>Cor Destacada no Calendário</th><td style='color: $color;'>Azul</td></tr>";
            } else if ($color == "#FF0831"){
                $dados .= "<tr><th>Cor Destacada no Calendário</th><td style='color: $color;'>Vermelho</td></tr>";
            }

            $start = formatarDataHoraBr($start); // formatando data de entrega para o padrão BR
            $dados .= "<tr><th>Data e Hora de Início do Evento</th><td>$start</td></tr>";
            $end = formatarDataHoraBr($end); // formatando data de entrega para o padrão BR
            $dados .= "<tr><th>Data e Hora de fim do Evento</th><td>$end</td></tr>";

            $dados .= "</td></tr>";
            $dados .= "</table>";

            // Adiciona uma linha horizontal após cada registro
            $dados .= "<hr>";
        }

        $dados .= "</body>";

        // Instanciar e usar a classe dompdf
        $dompdf = new Dompdf(['enable_remote' => true]);

        // Instanciar o metodo loadHtml e enviar o conteudo do PDF
        $dompdf->loadHtml($dados);

        // Configurar o tamanho e a orientacao do papel
        $dompdf->setPaper('A4', 'portrait');

        // Renderizar o HTML como PDF
        $dompdf->render();

        // Gerar o PDF
        $dompdf->stream();
    } else {
        echo "Erro: Falha na conexão com o banco de dados.";
    }
} catch (PDOException $e) {
    echo "Erro no PDO: " . $e->getMessage();
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
