<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;

class Home extends Component
{
    public $imagem = null; // imagem
    public $resultado = ''; // resultado se tem edição ou não
    public $confianca = ''; // porcentagem de confiança no resultado
    public $comentario = ''; // comentário extra da análise de ediçao
    public $respondido = false; // se já foi feita a análise ou não
    public $analise = ''; // análise descritiva da imagem

    public $errorMessage; // mensagem de erro se houver

    use WithFileUploads;
    public function render()
    {
        return view('livewire.home');
    }

    // função para enviar a imagem para ver se é editada
    public function enviarImagem()
    {
        // verifica se a imagem existe
        if ($imagem = $this->imagem->path()) {

            // transforma a imagem em um texto base64
            $base64Image = base64_encode(file_get_contents($imagem));
            try {
                // envia a imagem para a api
                $response = self::apiRequest('https://api.openai.com/v1/chat/completions', 'post', [
                    'model' => 'gpt-4o-mini', // modelo da IA
                    'messages' => [
                        (object)[ // descrição da role do sistema
                            'role' => 'system',
                            'content' => 'Você é um assistente que tem a função de descobrir se uma imagem foi editada digitalmente ou não, independente se for uma imagem vinda de uma foto ou uma ilustração que contenha edições. Responda com um JSON sem formatações no formato: {"resposta": "sim/não (se foi gerado por IA ou não)", "confianca": "0.0-1.0", "comentario": "comentário opcional"}',
                        ],
                        (object)[ // mensagem do usuário
                            'role' => 'user',
                            'content' => [
                                (object)[
                                    'type' => 'image_url',
                                    'image_url' => (object)[
                                        'url' => "data:image/jpeg;base64,$base64Image"
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]);
                // pega a resposta da api em forma de json
                $resposta = $response->json();
                $resposta = current($resposta['choices']);

                // pega o array que foi enviado pelo gpt
                $respostaArray = json_decode($resposta['message']['content'], true);

                // pega os valores do array e passa para as propriedades da classe
                $this->resultado = $respostaArray['resposta'];
                $this->confianca = $respostaArray['confianca'];
                $this->comentario = $respostaArray['comentario'];
                $this->respondido = true;
                $this->analisarImagem();
            } catch (\Exception $e) {
                // se deu erro na api mostra essa mensagem
                $this->errorMessage = 'Falha na requisição: ' . $e->getMessage();
                $this->respondido = false;
            }
        }else {
            // se não tiver selecionado imagem mostra essa mensagem
            $this->errorMessage = 'Selecione uma imagem!';
        }
    }

    // função para fazer a requisição da api
    public static function apiRequest($url, $method, $data = [])
    {
        // pega a chave da api do arquivo .env
        $apiKey = env('OPENAI_API_KEY');
        $method = strtolower($method);

        // retorna a resposta da requisição
        return Http::withOptions(['verify' => false])->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->$method($url, $data);
    }

    // função que olha quando muda a imagem
    public function updatedImagem()
    {
        // quando muda a imagem reseta o valor de todas as propriedades
        $this->respondido = false;
        $this->resultado = '';
        $this->confianca = '';
        $this->comentario = '';
        $this->analise = '';
    }

    // função pra enviar a imagem para análise para o GPT
    public function analisarImagem()
    {
        // verifica se a imagem existe
        if ($imagem = $this->imagem->path()) {
            // transforma a imagem em um texto base64
            $base64Image = base64_encode(file_get_contents($imagem));
            try {
                // envia uma requisição para a api do gpt
                $response = self::apiRequest('https://api.openai.com/v1/chat/completions', 'post', [
                    'model' => 'gpt-4o-mini', // modelo da IA
                    'messages' => [
                        (object)[ // descrição da role da IA
                            'role' => 'system',
                            'content' => 'Você é um especialista em descrição de imagens. Faça uma descrição detalhada da imagem que for enviada.',
                        ],
                        (object)[
                            'role' => 'user',
                            'content' => [ // mensagem do usuário
                                (object)[
                                    'type' => 'image_url',
                                    'image_url' => (object)[
                                        'url' => "data:image/jpeg;base64,$base64Image"
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]);

                // pega a resposta em forma de json
                $resposta = $response->json();
                $resposta = current($resposta['choices']);

                // pega a resposta da análise que o gpt retornouvu
                $this->analise = $resposta['message']['content'];

            } catch (\Exception $e) {
                // se deu erro na api mostra essa mensagem
                $this->errorMessage = 'Falha na requisição: ' . $e->getMessage();
                $this->respondido = false;
            }
        } else {
            // se não tiver texto mostra essa mensagem
            $this->errorMessage = 'Selecione uma imagem!';
        }
    }

}
