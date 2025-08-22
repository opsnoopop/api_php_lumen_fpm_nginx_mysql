<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;

class UsersController extends Controller
{
    public function __construct(private PDO $pdo) {}

    // POST /users  -> insert
    public function store(Request $request)
    {
        $username = trim((string)$request->input('username', ''));
        $email    = trim((string)$request->input('email', ''));

        if ($username === '' || $email === '') {
            return response()->json([
                'error' => 'username and email are required'
            ], 400);
        }

        $sql = "INSERT INTO users (username, email) VALUES (:username, :email)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
        ]);

        $id = (int)$this->pdo->lastInsertId();

        return response()->json([
            'message' => "User created successfully",
            'user_id' => $id
        ], 201);
    }

    // GET /users/{id}  -> select
    public function show(int $id)
    {
        $sql = "SELECT user_id, username, email
                FROM users
                WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $id]);
        $row = $stmt->fetch();

        if (!$row) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'user_id' => $row['user_id'],
            'username' => $row['username'],
            'email' => $row['email']
        ], 200);
    }
}
