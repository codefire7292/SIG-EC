<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'auditable_id',
        'auditable_type',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'agent_name',
        'agent_email',
        'agent_role',
        'action_label',
        'action_badge',
        'target_summary',
        'target_type',
        'target_url',
        'formatted_changes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    /**
     * Convenient static method to record an audit log.
     */
    public static function record(string $action, $auditable = null, array $extraMetadata = []): self
    {
        $user = Auth::user();
        $ip = request()?->ip() ?? '127.0.0.1';
        $userAgent = request()?->userAgent() ?? 'System';

        $baseMetadata = [
            'ip' => $ip,
            'user_agent' => $userAgent,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'user_role' => $user?->getRoleNames()->first(),
        ];

        if ($auditable && method_exists($auditable, 'getAuditTargetSummary')) {
            $baseMetadata['target_summary'] = $auditable->getAuditTargetSummary();
        } elseif ($auditable && isset($auditable->reference_number)) {
            $baseMetadata['target_summary'] = class_basename($auditable) . ' #' . $auditable->reference_number;
        } elseif ($auditable && isset($auditable->name)) {
            $baseMetadata['target_summary'] = class_basename($auditable) . ' ' . $auditable->name;
        }

        $metadata = array_merge($baseMetadata, $extraMetadata);

        return self::create([
            'user_id' => $user?->id ?? Auth::id(),
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Agent Name Accessor
     */
    public function getAgentNameAttribute(): string
    {
        return $this->user?->name ?? ($this->metadata['user_name'] ?? 'Système / Automatique');
    }

    /**
     * Agent Email Accessor
     */
    public function getAgentEmailAttribute(): string
    {
        return $this->user?->email ?? ($this->metadata['user_email'] ?? '—');
    }

    /**
     * Agent Role Accessor
     */
    public function getAgentRoleAttribute(): string
    {
        return $this->user?->getRoleNames()->first() ?? ($this->metadata['user_role'] ?? 'Non défini');
    }

    /**
     * Action Label in French
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'creation' => "Création d'acte / entrée",
            'modification' => 'Modification de données',
            'modification_manuelle' => 'Modification manuelle',
            'suppression' => 'Suppression',
            'validation' => 'Validation officielle',
            'signature' => 'Signature légale',
            'rejet' => "Rejet d'acte",
            'demande_correction' => 'Demande de correction',
            'consultation' => 'Consultation de fiche',
            'observation' => 'Ajout d\'observation',
            'rectification' => 'Rectification administrative',
            'telechargement_extrait' => 'Téléchargement d\'extrait',
            'impression' => 'Impression d\'extrait',
            'import' => 'Importation Excel',
            'connexion' => 'Connexion à la plateforme',
            'deconnexion' => 'Déconnexion',
            'echec_connexion' => 'Échec de connexion',
            'fermeture_registre' => 'Clôture de registre',
            'reouverture_registre' => 'Réouverture de registre',
            'suspension_compte' => 'Suspension de compte agent',
            'reactivation_compte' => 'Réactivation de compte agent',
            'changement_statut' => 'Changement de statut',
            'modification_mot_de_passe' => 'Changement de mot de passe',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Visual Badge Style for UI
     */
    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'creation' => [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'PlusCircleIcon',
            ],
            'validation' => [
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => 'CheckBadgeIcon',
            ],
            'signature' => [
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'dot' => 'bg-purple-600',
                'icon' => 'PencilSquareIcon',
            ],
            'modification', 'modification_manuelle', 'rectification' => [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'PencilIcon',
            ],
            'rejet', 'suppression', 'suspension_compte' => [
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'XCircleIcon',
            ],
            'demande_correction' => [
                'bg' => 'bg-orange-50 text-orange-700 border-orange-200',
                'dot' => 'bg-orange-500',
                'icon' => 'ExclamationTriangleIcon',
            ],
            'consultation' => [
                'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-500',
                'icon' => 'EyeIcon',
            ],
            'telechargement_extrait', 'impression' => [
                'bg' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                'dot' => 'bg-cyan-500',
                'icon' => 'ArrowDownTrayIcon',
            ],
            'connexion' => [
                'bg' => 'bg-teal-50 text-teal-700 border-teal-200',
                'dot' => 'bg-teal-500',
                'icon' => 'ArrowRightOnRectangleIcon',
            ],
            'deconnexion' => [
                'bg' => 'bg-gray-100 text-gray-700 border-gray-200',
                'dot' => 'bg-gray-500',
                'icon' => 'ArrowLeftOnRectangleIcon',
            ],
            'fermeture_registre', 'reouverture_registre' => [
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'dot' => 'bg-indigo-500',
                'icon' => 'BookOpenIcon',
            ],
            'import' => [
                'bg' => 'bg-violet-50 text-violet-700 border-violet-200',
                'dot' => 'bg-violet-500',
                'icon' => 'ArrowUpTrayIcon',
            ],
            default => [
                'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
                'dot' => 'bg-gray-400',
                'icon' => 'InformationCircleIcon',
            ],
        };
    }

    /**
     * Target summary description
     */
    public function getTargetSummaryAttribute(): string
    {
        if (!empty($this->metadata['target_summary'])) {
            return $this->metadata['target_summary'];
        }

        if ($this->auditable) {
            if (method_exists($this->auditable, 'getAuditTargetSummary')) {
                return $this->auditable->getAuditTargetSummary();
            }
            if (isset($this->auditable->reference_number)) {
                return class_basename($this->auditable) . ' #' . $this->auditable->reference_number;
            }
            if (isset($this->auditable->certificate_number)) {
                return 'Certificat #' . $this->auditable->certificate_number;
            }
            if (isset($this->auditable->name)) {
                return class_basename($this->auditable) . ' ' . $this->auditable->name;
            }
        }

        return match ($this->action) {
            'connexion', 'deconnexion' => 'Session agent',
            default => $this->auditable_type ? class_basename($this->auditable_type) . ' #' . ($this->auditable_id ?? '—') : 'Système',
        };
    }

    /**
     * Target Type category
     */
    public function getTargetTypeAttribute(): string
    {
        $type = $this->auditable_type;
        if (!$type) {
            return match ($this->action) {
                'connexion', 'deconnexion' => 'Authentification',
                default => 'Système',
            };
        }

        return match ($type) {
            'App\Models\BirthAct' => 'Acte de Naissance',
            'App\Models\MarriageAct' => 'Acte de Mariage',
            'App\Models\DeathAct' => 'Acte de Décès',
            'App\Models\CivilCertificate' => 'Certificat Civil',
            'App\Models\Registry' => 'Registre',
            'App\Models\User' => 'Agent / Utilisateur',
            'App\Models\CivilRegistrationCenter' => 'Centre d\'état-civil',
            'App\Models\Setting' => 'Configuration',
            default => class_basename($type),
        };
    }

    /**
     * Direct URL to inspect the target item if available
     */
    public function getTargetUrlAttribute(): ?string
    {
        if (!$this->auditable_id || !$this->auditable_type) {
            return null;
        }

        return match ($this->auditable_type) {
            'App\Models\BirthAct' => route('acts.naissance.show', $this->auditable_id, false),
            'App\Models\MarriageAct' => route('acts.mariage.show', $this->auditable_id, false),
            'App\Models\DeathAct' => route('acts.deces.show', $this->auditable_id, false),
            'App\Models\CivilCertificate' => route('civil-certificates.show', $this->auditable_id, false),
            'App\Models\Registry' => '/admin/registries',
            'App\Models\User' => '/admin/users',
            default => null,
        };
    }

    /**
     * Formatted Diff for modifications
     */
    public function getFormattedChangesAttribute(): array
    {
        $changes = $this->metadata['changes'] ?? [];
        $original = $this->metadata['original'] ?? [];

        if (empty($changes)) {
            return [];
        }

        $formatted = [];
        $ignoredFields = ['updated_at', 'password', 'remember_token'];

        foreach ($changes as $key => $newValue) {
            if (in_array($key, $ignoredFields)) {
                continue;
            }

            $oldValue = $original[$key] ?? null;

            $formatted[] = [
                'field' => $key,
                'label' => $this->humanFieldLabel($key),
                'old' => is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : (string) ($oldValue ?? '—'),
                'new' => is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : (string) ($newValue ?? '—'),
            ];
        }

        return $formatted;
    }

    /**
     * French field labels for human readability
     */
    protected function humanFieldLabel(string $field): string
    {
        return match ($field) {
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'date_of_birth' => 'Date de naissance',
            'place_of_birth' => 'Lieu de naissance',
            'gender' => 'Genre / Sexe',
            'status' => 'Statut de l\'acte',
            'reference_number' => 'Numéro de référence',
            'is_judgment' => 'Jugement supplétif',
            'judgment_number' => 'Numéro de jugement',
            'judgment_court' => 'Tribunal du jugement',
            'deceased_first_name' => 'Prénom du défunt',
            'deceased_last_name' => 'Nom du défunt',
            'date_of_death' => 'Date du décès',
            'place_of_death' => 'Lieu du décès',
            'husband_first_name' => 'Prénom de l\'époux',
            'husband_last_name' => 'Nom de l\'époux',
            'wife_first_name' => 'Prénom de l\'épouse',
            'wife_last_name' => 'Nom de l\'épouse',
            'marriage_date' => 'Date du mariage',
            'matrimonial_regime' => 'Régime matrimonial',
            'marriage_option' => 'Option de mariage',
            'name' => 'Nom complet',
            'email' => 'Adresse email',
            'is_active' => 'Statut actif',
            'telephone' => 'Téléphone',
            'adresse' => 'Adresse',
            'officer_comments' => 'Commentaires de l\'officier',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }
}
