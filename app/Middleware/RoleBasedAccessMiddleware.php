<?php
namespace App\Middleware;

use App\Services\AuthService;
use App\Repositories\UserRepository;

class RoleBasedAccessMiddleware {
    private $authService;
    private $userRepository;

    private $rolePermissions = [
        'admin' => [
            'view_all_applications',
            'manage_users',
            'generate_reports',
            'modify_system_settings'
        ],
        'clerk' => [
            'view_applications',
            'process_applications',
            'update_application_status'
        ],
        'applicant' => [
            'submit_application',
            'view_own_application',
            'update_personal_info'
        ]
    ];

    public function __construct(AuthService $authService, UserRepository $userRepository) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    public function handle(string $requiredPermission): bool {
        $user = $this->authService->getCurrentUser();

        if (!$user) {
            return false; // Not authenticated
        }

        $userRole = $this->userRepository->getUserRole($user['id']);

        // Check if user's role has the required permission
        return $this->checkPermission($userRole, $requiredPermission);
    }

    private function checkPermission(string $userRole, string $requiredPermission): bool {
        // Hierarchical access: admin can do everything
        if ($userRole === 'admin') {
            return true;
        }

        // Check if role has the required permission
        return isset($this->rolePermissions[$userRole]) && 
               in_array($requiredPermission, $this->rolePermissions[$userRole]);
    }

    public function enforceAccess(string $requiredPermission) {
        if (!$this->handle($requiredPermission)) {
            // Throw a custom access denied exception
            throw new \App\Exceptions\AccessDeniedException(
                "You do not have permission to perform this action."
            );
        }
    }

    public function getUserRoleHierarchy(): array {
        return [
            'admin' => ['clerk', 'applicant'],
            'clerk' => ['applicant'],
            'applicant' => []
        ];
    }
} 