<?php

namespace App\Models;

use App\Collections\StatusCollection;
use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Enum\NotTakenReason;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

/**
 * @property int              id
 * @property int              user_id
 * @property int              chain_id
 * @property string           body
 * @property Business         business
 * @property StatusVisibility visibility
 * @property int              event_id
 * @property string           tweet_id
 * @property string           mastodon_post_id
 * @property Checkin          $checkin
 * @property boolean          planned
 * @property boolean          taken
 * @property NotTakenReason   not_taken_reason
 *
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 */
class Status extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'chain_id', 'body', 'business', 'visibility', 'event_id', 'tweet_id', 'mastodon_post_id', 'client_id', 'planned', 'taken', 'not_taken_reason'];
    protected $hidden   = ['user_id', 'chain_id', 'business'];
    protected $appends  = ['favorited', 'socialText', 'statusInvisibleToMe', 'description'];
    protected $casts    = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'chain_id'         => 'integer',
        'business'         => Business::class,
        'visibility'       => StatusVisibility::class,
        'event_id'         => 'integer',
        'tweet_id'         => 'string',
        'mastodon_post_id' => 'string',
        'client_id'        => 'integer',
        'planned'          => 'boolean',
        'taken'            => 'boolean',
        'not_taken_reason' => NotTakenReason::class,
    ];

    public function newCollection(array $models = []): StatusCollection {
        return new StatusCollection($models);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function travelChain(): BelongsTo {
        return $this->belongsTo(TravelChain::class, 'chain_id');
    }

    public function canCheckin(): ?bool {
        if (!isset($this->travelChain) || isset($this->taken)) return false;
        // siehe statusbackend & travelchain cta*
        return $this->checkin->departure <= Carbon::parse('+5min');
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function checkin(): HasOne {
        return $this->hasOne(Checkin::class);
    }

    public function client(): BelongsTo {
        return $this->belongsTo(OAuthClient::class, 'client_id', 'id');
    }

    /**
     * @return HasOne
     * @deprecated use ->checkin instead
     */
    public function trainCheckin(): HasOne {
        return $this->checkin();
    }

    public function event(): HasOne {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function tags(): HasMany {
        return $this->hasMany(StatusTag::class, 'status_id', 'id');
    }

    public function getFavoritedAttribute(): ?bool {
        if (!Auth::check()) {
            return null;
        }
        return $this->likes->contains('user_id', Auth::id());
    }

    public function getSocialTextAttribute(): string {
        if (isset($this->event) && $this->event->hashtag !== null) {
            $postText = trans_choice(
                key:     'controller.transport.social-post-with-event',
                number:  preg_match('/\s/', $this->checkin->trip->linename),
                replace: [
                             'lineName'    => $this->checkin->trip->linename,
                             'destination' => $this->checkin->destinationStation->name,
                             'hashtag'     => $this->event->hashtag
                         ]
            );
        } else {
            $postText = trans_choice(
                key:     'controller.transport.social-post',
                number:  preg_match('/\s/', $this->checkin->trip->linename),
                replace: [
                             'lineName'    => $this->checkin->trip->linename,
                             'destination' => $this->checkin->destinationStation->name
                         ]
            );
        }


        if (isset($this->body)) {
            if ($this->event?->hashtag !== null) {
                $eventIntercept = __('controller.transport.social-post-for', [
                    'hashtag' => $this->event->hashtag
                ]);
            }

            $appendix = strtr(' (@ :linename ➜ :destination:eventIntercept) #NowTräwelling', [
                ':linename'       => $this->checkin->trip->linename,
                ':destination'    => $this->checkin->destinationStation->name,
                ':eventIntercept' => isset($eventIntercept) ? ' ' . $eventIntercept : ''
            ]);

            $appendixLength = strlen($appendix) + 30;
            $postText       = substr($this->body, 0, 280 - $appendixLength);
            if (strlen($postText) !== strlen($this->body)) {
                $postText .= '...';
            }
            $postText .= $appendix;
        }

        return $postText;
    }

    public function getDescriptionAttribute(): string {
        return __('description.status', [
            'username'    => $this->user->name,
            'origin'      => $this->checkin->originStation->name .
                             ($this->checkin->originStation->rilIdentifier ?
                                 ' (' . $this->checkin->originStation->rilIdentifier . ')' : ''),
            'destination' => $this->checkin->destinationStation->name .
                             ($this->checkin->destinationStation->rilIdentifier ?
                                 ' (' . $this->checkin->destinationStation->rilIdentifier . ')' : ''),
            'date'        => $this->checkin->departure->isoFormat(__('datetime-format')),
            'lineName'    => $this->checkin->trip->linename
        ]);
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }
}
