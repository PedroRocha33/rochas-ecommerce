<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Redemac</title>

    <link rel="icon" href="/assets/images/redemac-icon.jpg">
    <link rel="stylesheet" href="/rochas/assets/css/checkout.css">
</head>
<body>

<div class="checkout-page" id="checkout-page">

    <!-- ================= HEADER ================= -->
    <div class="checkout-header">
        <div class="checkout-header-content">

            <a href="<?= url("/") ?>"><button class="back-btn" type="button" onclick="backToStore()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m12 19-7-7 7-7"></path>
                    <path d="M19 12H5"></path>
                </svg>
                Voltar à Loja
            </button></a>

            <div class="checkout-logo">
                <img src="/rochas/assets/img/logo-redemac.png" alt="Redemac">
                <p>Finalizar Compra</p>
            </div>

            <div class="spacer"></div>
        </div>
    </div>

    <!-- ================= CONTENT ================= -->
    <div class="checkout-content">

        <!-- ================= FORMULÁRIO ================= -->
        <div class="customer-form">

            <h2>Dados do Cliente</h2>

            <form onsubmit="return false">

                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone *</label>
                    <input type="tel" id="telefone" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email">
                </div>

                <!-- ================= ENDEREÇO ================= -->
                <h3>Endereço de Entrega</h3>

                <div class="frete-box">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cep">CEP *</label>
                            <input type="text" id="cep" placeholder="00000-000">
                        </div>

                        <button type="button" class="btn-frete" onclick="calcularFrete()">
                            Calcular Frete
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="rua">Rua *</label>
                        <input type="text" id="rua">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero">Número *</label>
                            <input type="text" id="numero">
                        </div>

                        <div class="form-group">
                            <label for="complemento">Complemento</label>
                            <input type="text" id="complemento">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bairro">Bairro *</label>
                        <input type="text" id="bairro">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cidade">Cidade *</label>
                            <input type="text" id="cidade">
                        </div>

                        <div class="form-group">
                            <label for="estado">Estado *</label>
                            <input type="text" id="estado" maxlength="2">
                        </div>
                    </div>

                    <!-- FRETES -->
                    <div id="frete-opcoes"></div>
                    <br>
                    <label class="frete-opcao retirada">
                        <input type="radio" name="frete" value="0" onchange="selecionarFrete(0)">
                        Retirada na loja (Grátis)
                    </label>

                </div>

            </form>
        </div>

        <!-- ================= RESUMO ================= -->
        <div class="order-summary">

            <h2>Resumo do Pedido</h2>

            <div class="summary-items" id="summary-items"></div>

            <div class="summary-total">
                <span>Total:</span>
                <span class="summary-price" id="summary-price">R$ 0,00</span>
            </div>

            <button class="finalize-btn" type="button" onclick="finalizarCompraMP()">
                Finalizar via WhatsApp
            </button>

        </div>

    </div>
</div>

<!-- ================= SCRIPT ================= -->
<script src="/rochas/assets/js/checkout.js"></script>
<script src="/rochas/assets/js/mpService.js"></script>


</body>
</html>
