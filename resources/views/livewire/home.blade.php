<div>
    <div class="container mt-5 px-5">
        <div class="border rounded p-4 m-5" style="display: flex; flex-direction: column; row-gap: 20px">
            <div class="form-group">
                <label for="mensagem">
                    @if( !$respondido )
                        Selecione a imagem que gostaria verificar
                    @else
                        Caso queira, selecione outra imagem para verificar
                    @endif
                </label>
                <input type="file" class="form-control" id="mensagem" wire:model="imagem" placeholder="">
            </div>
            @if ( $imagem )
                <div class="d-flex justify-content-center w-100 rounded bg-dark" style="height: 350px">
                    <img src="{{ $imagem->temporaryUrl() }}" class="img-fluid text-center"
                          style="max-width: 350px; object-fit: fill" alt="Imagem enviada">
                </div>
                @if ( !$respondido )
                    <form class="d-flex justify-content-center" wire:submit="enviarImagem">
                        <button class="btn btn-primary p-1 px-5 m-3" >Enviar</button>
                    </form>
                @endif
            @endif
            @if ( $analise )
                <div class="form-group border border-info bg-gray-light p-3 rounded col-12">
                    <label>Análise Descritiva: </label> <span> {{ $analise }}</span>
                </div>
            @endif
            @if ( $respondido )
                <div class="d-flex justify-content-center" style="column-gap: 12px">
                    <div class="d-inline-flex border border-info bg-gray-light p-3 rounded col-4">
                        <div class="col-12">
                            <label>Imagem editada: </label>
                            <span> {{ $resultado }}</span>
                        </div>
                    </div>
                    <div class="d-inline-flex border border-info bg-gray-light p-3 rounded col-4">
                        <div class="col-12">
                            <label>Confiança: </label>
                            <span> {{ floatval($confianca) * 100 }} %</span>
                        </div>
                    </div>
                </div>
                <div class="form-group border border-info bg-gray-light p-3 rounded">
                    <label>Comentário: </label>
                    <span> {{ $comentario }}</span>
                </div>
            @endif

            @if ( $errorMessage && !$respondido )
                <div class="alert alert-danger my-2">
                    {{ $errorMessage }}
                </div>
            @endif
        </div>
    </div>
</div>
