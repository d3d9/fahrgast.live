<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\Likert5;
use App\Enum\TravelChainFinished;
// use App\Enum\StatusVisibility;
use App\Http\Controllers\StatusController as StatusBackend;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Collections\StatusCollection;

/**
 * @property int              id
 * @property int              user_id
 * @property string           title
 * @property string           body
 * @property ?Business        business
 * @property ?Likert5         reliability_importance
 * @property ?Likert5         planned_for_reliability
 * @property ?TravelChainFinished finished
 * @property ?Likert5         felt_punctual
 * @property ?Likert5         felt_stressed
 * @property Status[]         $statuses
 *
 */
class TravelChain extends Model
{

    // use HasFactory;

    protected $fillable = ['user_id', 'title', 'body', 'business', 'reliability_importance', 'planned_for_reliability', 'finished', 'felt_punctual', 'felt_stressed'];
    protected $hidden   = ['user_id'];
    protected $appends  = [];
    protected $casts    = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'business'         => Business::class,
        'reliability_importance' => Likert5::class,
        'planned_for_reliability' => Likert5::class,
        'finished'         => TravelChainFinished::class,
        'felt_punctual' => Likert5::class,
        'felt_stressed' => Likert5::class,
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class, 'chain_id', 'id')->with(['user', 'travelChain', 'checkin',
                                'checkin.originStation', 'checkin.destinationStation',
                                'checkin.trip.stopovers.station']);
    }

    public function plannedStatuses(): HasMany {
        return $this->statuses()->where('planned', true);
    }

    public function getPlannedStatusesAttribute(): StatusCollection {
        return $this->plannedStatuses()->get()->sortBy(function($status) {
            return $status->checkin->originStopover?->departure_planned;
        });
    }

    public function takenStatuses(): HasMany {
        return $this->statuses()->where('taken', true);
    }

    public function getTakenStatusesAttribute(): StatusCollection {
        return $this->takenStatuses()->get()->sortBy(function($status) {
            return $status->checkin->displayDeparture->time;
        });
    }

    public function ctaStatuses(): HasMany {
        return StatusBackend::ctaCheckinQuery($this);
    }

    public function getCtaStatusesAttribute(): StatusCollection {
        return StatusBackend::getCtaCheckins($this);
    }

    public function pendingStatuses(): HasMany {
        return $this->statuses()->where('taken', null);
    }

    /*public function getPendingStatusesAttribute(): StatusCollection {
        return $this->pendingStatuses()->get();
    }*/

    public function undefStatuses(): HasMany {
        // nur noch: ungeplante abfahrten, die aber taken = 0 sind
        return $this->statuses()->where(function($query) {
            $query->where('planned', false)->orWhere('planned', null);
        })->where(function($query) {
            $query->where('taken', false);/*->orWhere(function($query) {
                $query->where('taken', null)
                      ->whereHas('checkin', function($query) { // wie in statuscontroller
                         $query->where('departure', '>', date('Y-m-d H:i:s', strtotime('+5min')));
                      });
            });*/
        });
    }

    /*public function getUndefStatusesAttribute(): StatusCollection {
        return $this->undefStatuses()->get();
    }*/

    public function dataPending(): bool {
        if (isset($this->business) && isset($this->reliability_importance) && isset($this->planned_for_reliability)) {
            return false;
        }
        return true;
    }

    // used for frontend CTA
    public function endDataPending($totalCount = NULL, $pendingCount = NULL, $undefCount = NULL): bool {
        if (!$this->canFinish($totalCount, $pendingCount, $undefCount)) return false;
        if (isset($this->finished)
            && (!$this->finished->isArrived() || isset($this->felt_punctual))
            && isset($this->felt_stressed)
        ) {
            return false;
        }
        return true;
    }

    // prereqs for enddatapending
    public function canFinish($totalCount = NULL, $pendingCount = NULL, $undefCount = NULL): bool {
        // if (isset($this->finished)) return null;
        if ($this->dataPending()) return false;
        if (($totalCount ?? $this->statuses()->count()) == 0
         || ($pendingCount ?? $this->pendingStatuses()->count()) > 0
         || ($undefCount ?? $this->undefStatuses()->count()) > 0
        ) return false;
        return true;
    }

}
