<?php

namespace Gupalo\AuditLogBundle\Controller;

use Gupalo\AuditLogBundle\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuditLogController extends AbstractController
{
    public function __construct(private readonly AuditLogRepository $repository)
    {
    }

    #[Route(path: '/audit-log', name: 'audit_log_list', methods: ['GET'])]
    public function lists(): Response
    {
        return $this->render('@AuditLog/lists.html.twig', [
            'items' => $this->repository->findAll()
        ]);
    }
}
