<?php
class Post
{
    private $pdo;

    # Constructor
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function sendPayload($data, $remarks, $message, $code)
    {
        $status = array("remarks" => $remarks, "message" => $message);
        http_response_code($code);

        return array(
            "status" => $status,
            "data" => $data,  // Include data in the response
            "prepared_by" => "KanbanApp",
            "timestamp" => date_create(),
            "code" => $code  // Include code in the response
        );
    }

    public function executeQuery($sqlString)
    {
        $data = array();
        $errmsg = "";
        $code = 0;

        try {
            if ($result = $this->pdo->query($sqlString)->fetchAll()) {
                foreach ($result as $record) {
                    array_push($data, $record);
                }
                $code = 200;
                $result = null;
                return array("code" => $code, "data" => $data);
            } else {
                $errmsg = "No data found";
                $code = 404;
            }
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code" => $code, "errmsg" => $errmsg);
    }

    public function createTask($data)
    {
        // Task properties
        $name = $data->name;
        $status = $data->status;
        // Insert query
        $insertSql = "INSERT INTO `task` (`name`, `status`) 
                      VALUES (:name,:status)";

        $insertStmt = $this->pdo->prepare($insertSql);

        // Bind parameters
        $insertStmt->bindParam(':name', $name);
        $insertStmt->bindParam(':status', $status);

        // Execute SQL
        try {
            $insertStmt->execute();
            $taskId = $this->pdo->lastInsertId(); // Get the task ID after insertion

            return $this->sendPayload(
                array("task_id" => $taskId),
                "success",
                "Task created successfully",
                201
            );
        } catch (\PDOException $e) {
            return $this->sendPayload(
                null,
                "error",
                "Failed to create task: " . $e->getMessage(),
                500
            );
        }
    }

    public function updateTask($data)
    {
        // Validate and extract task properties
        $id = $data->id ?? null;
        $order = $data->order ?? null;
        $status = $data->status ?? null;

        if (!$id || $order === null || !$status) {
            return $this->sendPayload(
                null,
                "error",
                "Task ID, order, and status are required",
                400
            );
        }

        // SQL statement to update task
        $updateSql = "UPDATE task SET `order` = :order, `status` = :status WHERE id = :id";
        $updateStmt = $this->pdo->prepare($updateSql);

        // Bind parameters to prevent SQL injection
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->bindParam(':order', $order, PDO::PARAM_INT);
        $updateStmt->bindParam(':status', $status, PDO::PARAM_STR);

        try {
            if ($updateStmt->execute()) {
                // Check if any rows were updated
                if ($updateStmt->rowCount() > 0) {
                    return $this->sendPayload(
                        ["task_id" => $id],
                        "success",
                        "Task updated successfully",
                        200
                    );
                } else {
                    // No rows affected - possibly because the ID was not found
                    return $this->sendPayload(
                        null,
                        "error",
                        "No task found with the provided ID",
                        404
                    );
                }
            } else {
                // Execution failed for some reason
                return $this->sendPayload(
                    null,
                    "error",
                    "Failed to update task",
                    500
                );
            }
        } catch (\PDOException $e) {
            // Return error response on exception
            return $this->sendPayload(
                null,
                "error",
                "Error updating task: " . $e->getMessage(),
                500
            );
        }
    }

    public function deleteTask($data)
    {
        // Validate that the task ID is provided
        $id = $data->id ?? null;

        if (!$id) {
            return $this->sendPayload(
                null,
                "error",
                "Task ID is required",
                400
            );
        }

        // SQL statement to delete the task
        $deleteSql = "DELETE FROM task WHERE id = :id";
        $deleteStmt = $this->pdo->prepare($deleteSql);

        // Bind the task ID parameter
        $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

        try {
            if ($deleteStmt->execute()) {
                // Check if a task was actually deleted
                if ($deleteStmt->rowCount() > 0) {
                    return $this->sendPayload(
                        ["task_id" => $id],
                        "success",
                        "Task deleted successfully",
                        200
                    );
                } else {
                    return $this->sendPayload(
                        null,
                        "error",
                        "No task found with the provided ID",
                        404
                    );
                }
            } else {
                return $this->sendPayload(
                    null,
                    "error",
                    "Failed to delete task",
                    500
                );
            }
        } catch (\PDOException $e) {
            return $this->sendPayload(
                null,
                "error",
                "Error deleting task: " . $e->getMessage(),
                500
            );
        }
    }

    public function editTaskName($data)
    {
        // Validate that both ID, new name, and new due date are provided
        $id = $data->id ?? null;
        $newName = $data->name ?? null;
        $newDueDate = $data->dueDate ?? null;

        if (!$id || !$newName || !$newDueDate) {
            return $this->sendPayload(
                null,
                "error",
                "Task ID, new name, and new due date are required",
                400
            );
        }

        // SQL query to update the task name and due date
        $updateSql = "UPDATE task SET `name` = :name, `dueDate` = :due_date WHERE id = :id";
        $updateStmt = $this->pdo->prepare($updateSql);

        // Bind parameters
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->bindParam(':name', $newName, PDO::PARAM_STR);
        $updateStmt->bindParam(':due_date', $newDueDate, PDO::PARAM_STR);

        try {
            if ($updateStmt->execute()) {
                // Check if any rows were updated
                if ($updateStmt->rowCount() > 0) {
                    return $this->sendPayload(
                        ["task_id" => $id, "new_name" => $newName, "new_due_date" => $newDueDate],
                        "success",
                        "Task name and due date updated successfully",
                        200
                    );
                } else {
                    return $this->sendPayload(
                        null,
                        "error",
                        "No task found with the provided ID",
                        404
                    );
                }
            } else {
                return $this->sendPayload(
                    null,
                    "error",
                    "Failed to update task name and due date",
                    500
                );
            }
        } catch (\PDOException $e) {
            return $this->sendPayload(
                null,
                "error",
                "Error updating task name and due date: " . $e->getMessage(),
                500
            );
        }
    }
}
