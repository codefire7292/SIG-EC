<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Display the Audit & Control Module index page.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with(['user' => function ($q) {
            $q->withTrashed();
        }, 'auditable' => function ($q) {
            $q->withTrashed();
        }])->latest('id');

        // Filter by Agent
        $userId = $request->query('user_id');
        if ($userId && $userId !== 'all') {
            $query->where('user_id', $userId);
        }

        // Filter by Action
        $action = $request->query('action');
        if ($action && $action !== 'all') {
            if ($action === 'modification_group') {
                $query->whereIn('action', ['modification', 'modification_manuelle', 'rectification']);
            } elseif ($action === 'auth_group') {
                $query->whereIn('action', ['connexion', 'deconnexion', 'modification_mot_de_passe']);
            } else {
                $query->where('action', $action);
            }
        }

        // Filter by Target / Module Type
        $targetType = $request->query('target_type');
        if ($targetType && $targetType !== 'all') {
            match ($targetType) {
                'naissance' => $query->where('auditable_type', \App\Models\BirthAct::class),
                'mariage' => $query->where('auditable_type', \App\Models\MarriageAct::class),
                'deces' => $query->where('auditable_type', \App\Models\DeathAct::class),
                'certificat' => $query->where('auditable_type', \App\Models\CivilCertificate::class),
                'registre' => $query->where('auditable_type', \App\Models\Registry::class),
                'utilisateur' => $query->where('auditable_type', \App\Models\User::class),
                'auth' => $query->whereIn('action', ['connexion', 'deconnexion', 'modification_mot_de_passe']),
                default => null,
            };
        }

        // Filter by Date From
        $dateFrom = $request->query('date_from');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        // Filter by Date To
        $dateTo = $request->query('date_to');
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Text Search (Agent Name, Email, Reference, IP, Action)
        $search = $request->query('search');
        if ($search) {
            $term = '%' . mb_strtolower(trim($search), 'UTF-8') . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(action) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(metadata::text) LIKE ?', [$term])
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->whereRaw('LOWER(name) LIKE ?', [$term])
                         ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                  });
            });
        }

        // KPIs calculation
        $totalActivities = AuditLog::count();
        $todayActivities = AuditLog::whereDate('created_at', today())->count();
        $activeAgentsCount = AuditLog::distinct('user_id')->whereNotNull('user_id')->count();
        $validatedAndSignedCount = AuditLog::whereIn('action', ['validation', 'signature'])->count();

        // Paginate logs
        $logs = $query->paginate(20)->withQueryString();

        // Agents list for filter
        $agents = User::withTrashed()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ];
            });

        // Available actions for filtering
        $availableActions = [
            ['value' => 'all', 'label' => 'Toutes les actions'],
            ['value' => 'creation', 'label' => 'Création d\'actes / fiches'],
            ['value' => 'modification_group', 'label' => 'Modifications de données'],
            ['value' => 'validation', 'label' => 'Validations officielles'],
            ['value' => 'signature', 'label' => 'Signatures légales'],
            ['value' => 'rejet', 'label' => 'Rejets'],
            ['value' => 'demande_correction', 'label' => 'Demandes de correction'],
            ['value' => 'consultation', 'label' => 'Consultations de fiches'],
            ['value' => 'telechargement_extrait', 'label' => 'Téléchargements d\'extraits'],
            ['value' => 'import', 'label' => 'Imports Excel'],
            ['value' => 'fermeture_registre', 'label' => 'Clôtures de registre'],
            ['value' => 'reouverture_registre', 'label' => 'Réouvertures de registre'],
            ['value' => 'auth_group', 'label' => 'Connexions & Sessions'],
            ['value' => 'suspension_compte', 'label' => 'Suspensions de compte'],
            ['value' => 'reactivation_compte', 'label' => 'Réactivations de compte'],
        ];

        // Available target types
        $availableTypes = [
            ['value' => 'all', 'label' => 'Tous les modules'],
            ['value' => 'naissance', 'label' => 'Actes de Naissance'],
            ['value' => 'mariage', 'label' => 'Actes de Mariage'],
            ['value' => 'deces', 'label' => 'Actes de Décès'],
            ['value' => 'certificat', 'label' => 'Certificats Civils'],
            ['value' => 'registre', 'label' => 'Registres & Volumes'],
            ['value' => 'utilisateur', 'label' => 'Agents & Utilisateurs'],
            ['value' => 'auth', 'label' => 'Sécurité & Authentification'],
        ];

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'stats' => [
                'total_activities' => $totalActivities,
                'today_activities' => $todayActivities,
                'active_agents_count' => $activeAgentsCount,
                'validated_and_signed_count' => $validatedAndSignedCount,
            ],
            'filters' => [
                'user_id' => $userId ?? 'all',
                'action' => $action ?? 'all',
                'target_type' => $targetType ?? 'all',
                'date_from' => $dateFrom ?? '',
                'date_to' => $dateTo ?? '',
                'search' => $search ?? '',
            ],
            'agents' => $agents,
            'availableActions' => $availableActions,
            'availableTypes' => $availableTypes,
        ]);
    }

    /**
     * Return specific log detail for modal inspection.
     */
    public function show(int $id)
    {
        $log = AuditLog::with(['user' => function ($q) {
            $q->withTrashed();
        }, 'auditable' => function ($q) {
            $q->withTrashed();
        }])->findOrFail($id);

        return response()->json([
            'log' => $log,
        ]);
    }

    /**
     * Export filtered logs to CSV format.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::with(['user' => function ($q) {
            $q->withTrashed();
        }])->latest('id');

        // Apply same filters as index
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action') && $request->action !== 'all') {
            if ($request->action === 'modification_group') {
                $query->whereIn('action', ['modification', 'modification_manuelle', 'rectification']);
            } elseif ($request->action === 'auth_group') {
                $query->whereIn('action', ['connexion', 'deconnexion', 'modification_mot_de_passe']);
            } else {
                $query->where('action', $request->action);
            }
        }
        if ($request->filled('target_type') && $request->target_type !== 'all') {
            match ($request->target_type) {
                'naissance' => $query->where('auditable_type', \App\Models\BirthAct::class),
                'mariage' => $query->where('auditable_type', \App\Models\MarriageAct::class),
                'deces' => $query->where('auditable_type', \App\Models\DeathAct::class),
                'certificat' => $query->where('auditable_type', \App\Models\CivilCertificate::class),
                'registre' => $query->where('auditable_type', \App\Models\Registry::class),
                'utilisateur' => $query->where('auditable_type', \App\Models\User::class),
                'auth' => $query->whereIn('action', ['connexion', 'deconnexion', 'modification_mot_de_passe']),
                default => null,
            };
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $term = '%' . mb_strtolower(trim($request->search), 'UTF-8') . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(action) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(metadata::text) LIKE ?', [$term])
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->whereRaw('LOWER(name) LIKE ?', [$term])
                         ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                  });
            });
        }

        $filename = 'journaux_audit_sig_ec_' . date('Y_m_d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($output, [
                'ID',
                'Date et Heure',
                'Agent (Nom)',
                'Agent (Email)',
                'Rôle',
                'Action',
                'Élément concerné',
                'Module / Type',
                'Adresse IP',
                'Navigateur',
                'Détails / Changements',
            ], ';');

            $query->chunk(500, function ($logs) use ($output) {
                foreach ($logs as $log) {
                    $changesSummary = '';
                    if (!empty($log->formatted_changes)) {
                        $diffs = [];
                        foreach ($log->formatted_changes as $c) {
                            $diffs[] = "{$c['label']}: '{$c['old']}' -> '{$c['new']}'";
                        }
                        $changesSummary = implode(' | ', $diffs);
                    }

                    fputcsv($output, [
                        $log->id,
                        $log->created_at->format('d/m/Y H:i:s'),
                        $log->agent_name,
                        $log->agent_email,
                        $log->agent_role,
                        $log->action_label,
                        $log->target_summary,
                        $log->target_type,
                        $log->metadata['ip'] ?? '—',
                        $log->metadata['user_agent'] ?? '—',
                        $changesSummary,
                    ], ';');
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
