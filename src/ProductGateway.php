<?php

class ProductGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM posts";
        $stmt = $this->conn->query($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //in foreach loop we use & operator to actually modify array otherwise array would not be affected
        foreach ($data as &$d) {
            $d["isAccepted"] = (bool)$d["isAccepted"];
        }
        return $data;
    }

    public function create(array $data): string
    {
        $sql = "INSERT INTO posts (title,content,isAccepted) VALUES (:title,:content,:isAccepted)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":title", $data["title"], PDO::PARAM_STR);
        $stmt->bindValue(":content", $data["content"], PDO::PARAM_STR);
        $stmt->bindValue(":isAccepted", $data["isAccepted"], PDO::PARAM_INT);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function get(string $id): array|false
    {
        $sql = "SELECT * FROM posts WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data !== false) {
            $data["isAccepted"] = (bool)$data["isAccepted"];
        }
        return $data;

    }

    public function update(array $current, array $new): int
    {
        $sql = "UPDATE posts SET title=:title,content=:content,isAccepted=:isAccepted WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":title", $new["title"] ?? $current["title"], PDO::PARAM_STR);
        $stmt->bindValue(":content", $new["content"] ?? $current["content"], PDO::PARAM_STR);
        $stmt->bindValue(":isAccepted", $new["isAccepted"] ?? $current["isAccepted"], PDO::PARAM_INT);
        $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete(string $id):int
    {
        $sql = "DELETE FROM posts WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id",$id,PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}