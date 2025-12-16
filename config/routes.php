<?php

use App\Controller\DashboardController;
use App\Controller\JobBoardController;
use App\Controller\LandingController;
use App\Controller\RegistrationController;
use App\Controller\SecurityController;
use App\Controller\UserController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('app_home', '/')
        ->controller([LandingController::class, 'index'])
        ->methods(['GET']);

    $routes->add('app_login', '/login')
        ->controller([SecurityController::class, 'login'])
        ->methods(['GET', 'POST']);

    $routes->add('app_logout', '/logout')
        ->controller([SecurityController::class, 'logout'])
        ->methods(['GET', 'POST']);

    $routes->add('app_register', '/register')
        ->controller([RegistrationController::class, 'register'])
        ->methods(['GET', 'POST']);

    $routes->add('app_dashboard', '/dashboard')
        ->controller([DashboardController::class, 'index'])
        ->methods(['GET']);

    $routes->add('app_user_profile', '/profile')
        ->controller([UserController::class, 'profile'])
        ->methods(['GET']);

    $routes->add('app_user_profile_update', '/profile/update')
        ->controller([UserController::class, 'updateProfile'])
        ->methods(['POST']);

    $routes->add('app_user_change_password', '/profile/change-password')
        ->controller([UserController::class, 'changePassword'])
        ->methods(['POST']);

    $routes->add('fragment_job_board_modal', '/job-board/_fragment/job-board-modal')
        ->controller([JobBoardController::class, 'modal'])
        ->methods(['GET']);

    $routes->add('fragment_job_board_edit_modal', '/job-board/{id}/_fragment/edit-modal')
        ->controller([JobBoardController::class, 'editModal'])
        ->methods(['GET'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_board_create', '/job-board/create')
        ->controller([JobBoardController::class, 'create'])
        ->methods(['POST']);

    $routes->add('app_job_board_update', '/job-board/{id}/update')
        ->controller([JobBoardController::class, 'update'])
        ->methods(['POST'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_board_delete', '/job-board/{id}/delete')
        ->controller([JobBoardController::class, 'delete'])
        ->methods(['DELETE'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_board_kanban', '/job-board/{id}/kanban')
        ->controller([JobBoardController::class, 'kanban'])
        ->methods(['GET'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_application_details', '/job-board/application/{id}/details')
        ->controller([JobBoardController::class, 'applicationDetails'])
        ->methods(['GET'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_application_update_status', '/job-board/application/{id}/status')
        ->controller([JobBoardController::class, 'updateStatus'])
        ->methods(['PATCH'])
        ->requirements(['id' => '\d+']);

    $routes->add('fragment_job_application_modal', '/job-board/{boardId}/_fragment/application-modal')
        ->controller([JobBoardController::class, 'applicationModal'])
        ->methods(['GET'])
        ->requirements(['boardId' => '\d+']);

    $routes->add('app_job_application_create', '/job-board/{boardId}/application/create')
        ->controller([JobBoardController::class, 'createApplication'])
        ->methods(['POST'])
        ->requirements(['boardId' => '\d+']);

    $routes->add('fragment_job_application_edit_modal', '/job-board/application/{id}/_fragment/edit-modal')
        ->controller([JobBoardController::class, 'editApplicationModal'])
        ->methods(['GET'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_application_update', '/job-board/application/{id}/update')
        ->controller([JobBoardController::class, 'updateApplication'])
        ->methods(['POST'])
        ->requirements(['id' => '\d+']);

    $routes->add('app_job_application_delete', '/job-board/application/{id}/delete')
        ->controller([JobBoardController::class, 'deleteApplication'])
        ->methods(['DELETE'])
        ->requirements(['id' => '\d+']);
};

