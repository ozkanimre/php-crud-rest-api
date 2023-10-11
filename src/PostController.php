<?php

class PostController
{
    public function __construct(private ProductGateway $gateway)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->processResourceRequest($method, $id);
        } else {
            $this->processCollectionRequest($method);
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $post = $this->gateway->get($id);

        if (!$post) {
            http_response_code(404);
            echo json_encode(["message" => "Post not found."]);
            return;
        }

        switch ($method) {
            case 'GET':
                echo json_encode($post);
                break;
            case 'PATCH':
                $data = (array)json_decode(file_get_contents("php://input"), true);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                $rows = $this->gateway->update($post, $data);
                echo json_encode([
                    "message" => "Post $id updated.",
                    "rows" => $rows
                ]);
                break;
            case 'DELETE':
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "Post $id deleted",
                    "rows" => $rows
                ]);
                break;
            default:
                http_response_code(405);
                header("Allow:GET,PATCH,DELETE");
        }

    }

    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case 'GET':
                echo json_encode($this->gateway->getAll());
                break;
            case 'POST':
                $data = (array)json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($data);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                $id = $this->gateway->create($data);
                http_response_code(201);
                echo json_encode([
                    "message" => "Post is created",
                    "id" => $id
                ]);
                break;
            default:
                http_response_code(405);
                header("Allow:GET,POST");
        }
    }

    private function getValidationErrors(array $data): array
    {
        $errors = [];
        if (empty($data["title"])) {
            $errors[] = "title is required";
        } else if (empty($data["content"])) {
            $errors[] = "content is required";
        }
        return $errors;
    }
}