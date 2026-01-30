<?php

namespace Source\WebService;

use Source\Core\Connect;
use Source\Models\Order;
use Source\Models\User; // Assumindo que você tenha um model User

class Orders extends Api
{
    /**
     * LISTAR PEDIDOS
     * GET /api/orders
     */
    public function listOrders(): void
    {
        $this->auth(); // Protege a rota

        $orderModel = new Order();
        // Filtra por usuário se o ID for passado na URL, caso contrário lista todos
        $userId = $_GET["usuario_id"] ?? null;
        
        if ($userId) {
            $orders = $orderModel->findByUserId((int)$userId);
        } else {
            // Reaproveita o findAll do Model genérico
            $orders = $orderModel->findAll(); 
        }

        $this->call(200, "success", "Lista de pedidos", "success")
            ->back($orders);
    }

    /**
     * CRIAR PEDIDO
     * POST /api/orders
     */
    public function createOrder(array $data): void
    {
        // $this->auth();

        if (empty($data)) {
        $data = json_decode(file_get_contents("php://input"), true) ?? [];
    }

        // Validação básica de campos obrigatórios
        if (empty($data["usuario_id"]) || empty($data["total"])) {
            $this->call(400, "bad_request", "Usuário e total são obrigatórios", "error")->back();
            return;
        }

        // ===== CRIA O PEDIDO =====
        $order = new Order(
            null,
            (int) $data["usuario_id"],
            (float) ($data["subtotal"] ?? 0),
            (float) $data["total"],
            $data["status"] ?? "pending",
            (float) ($data["valor_frete"] ?? 0),
            $data["tipo_frete"] ?? null,
            (int) ($data["prazo_frete"] ?? 0)
        );

        if (!$order->insert()) {
            $this->call(500, "internal_server_error", "Erro ao registrar pedido", "error")->back();
            return;
        }

        $this->call(201, "created", "Pedido realizado com sucesso", "success")->back([
            "id" => $order->getId(),
            "status" => $order->getStatus(),
            "total" => $order->getTotal()
        ]);
    }

    /**
     * BUSCAR PEDIDO POR ID
     * GET /api/orders/{id}
     */
    public function getOrderById(array $data): void
    {
        $this->auth();

        if (empty($data["id"]) || !filter_var($data["id"], FILTER_VALIDATE_INT)) {
            $this->call(400, "bad_request", "ID inválido", "error")->back();
            return;
        }

        $order = new Order();
        if (!$order->findById($data["id"])) {
            $this->call(404, "not_found", "Pedido não encontrado", "error")->back();
            return;
        }

        $this->call(200, "success", "Pedido encontrado", "success")
            ->back([
                "id" => $order->getId(),
                "usuario_id" => $order->getUsuarioId(),
                "subtotal" => $order->getSubtotal(),
                "total" => $order->getTotal(),
                "status" => $order->getStatus(),
                "criado_em" => $order->getCriadoEm(),
                "valor_frete" => $order->getValorFrete(),
                "tipo_frete" => $order->getTipoFrete(),
                "prazo_frete" => $order->getPrazoFrete()
            ]);
    }

    /**
     * ATUALIZAR STATUS DO PEDIDO
     * PUT /api/orders/{id}
     */
    public function updateOrderStatus(array $data): void
    {
        $this->auth();

        if (empty($data["id"]) || empty($data["status"])) {
            $this->call(400, "bad_request", "ID e Status são obrigatórios", "error")->back();
            return;
        }

        $order = new Order();
        if (!$order->findById($data["id"])) {
            $this->call(404, "not_found", "Pedido não encontrado", "error")->back();
            return;
        }

        $order->setStatus($data["status"]);

        if (!$order->update()) {
            $this->call(500, "internal_server_error", "Erro ao atualizar status", "error")->back();
            return;
        }

        $this->call(200, "success", "Status do pedido atualizado", "success")->back();
    }
}