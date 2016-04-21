<?php
/**
 * This file contains the Lead Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2016, Satoshi Payne
 */

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;

// 3rd Party
use Carbon\Carbon;
use Stevebauman\EloquentTable\TableTrait;

/**
 * The Lead Model Class, based on database entries in the 'lead' table.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @category  Models
 * @package   Lead
 */
class Lead extends Model implements PickupableInterface
{
	// App
	use CommonTrait;
	use PickupableTrait;

	// 3rd Party
	use TableTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'lead';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'claimed_by',
		'converted_by',
		'customer_id',
		'status',
		'name',
		'phone',
		'email',
		'message',
		'lead_data',
		'pickedup_by',
		'pickedup_at',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'claimed_at',
		'converted_at',
		'next_contact_at',
	];

	/// Section: Properties

	const STATUS_ALIASES = [
		'new'       => ['new'],
		'active'    => ['new', 'requeued', 'cold', 'claimed'],
		'unclaimed' => ['new', 'requeued', 'cold'],
		'converted' => ['converted'],
		'inactive'  => ['duplicate', 'discarded', 'failed'],
	];

	/// Section: Relations

	public function interactions()
	{
		return $this->hasMany(LeadInteraction::class);
	}

	public function pickups()
	{
		return $this->hasMany(LeadPickup::class);
	}

	public function claims()
	{
		return $this->hasMany(LeadClaim::class);
	}

	/// Section: Repository

	public static function getPickedUpLeadBy($user)
	{
		return static::query()
			->whereIn('id', function($query) use ($user) {
				$query->select('lead_id')
					->from('lead_pickup')
					->whereNull('requeued_at')
					->where('sales_id', '=', $user->id)
					->where('status', '=', 'open');
			})
			->first();
	}

	public static function getNextAvailableLead()
	{
		// get unclaimed statuses
		$aliases = static::STATUS_ALIASES;
		$unclaimed = $aliases['unclaimed'];

		// return lead
		return static::query()
			->whereNull('claimed_by')
			->whereNull('converted_by')
			->where('next_contact_at', '<', Carbon::now())
			->whereIn('status', $unclaimed)
			->whereNotIn('id', function($query) {
				$query->select('lead_id')
					->from('lead_pickup')
					->whereNull('requeued_at')
					->where('status', '=', 'open');
			})
			->orderBy('next_contact_at', 'asc')
			->first();
	}

	public static function getIdleLeads()
	{
		// search lead status new and picked up time exceeds 15 minutes
		$time = Carbon::now()->subMinutes(15);

		// get unclaimed statuses
		$aliases = static::STATUS_ALIASES;
		$unclaimed = $aliases['unclaimed'];

		// return leads
		return static::with([
			'pickups' => function($query) use ($time) {
					$query->whereNull('requeued_at')
						->where('updated_at', '<', $time)
						->where('status', '=', 'open');
				}])
			->whereIn('status', $unclaimed)
			->whereIn('id', function($query) use ($time) {
				$query->select('lead_id')
					->from('lead_pickup')
					->whereNull('requeued_at')
					->where('updated_at', '<', $time)
					->where('status', '=', 'open');
			})
			->get();
	}

	/// Section: Methods

	public static function refreshLeads()
	{
		// get idle leads
		$idleLeads = static::getIdleLeads();

		// requeue idle leads as 'staff idle'
		foreach($idleLeads as $lead){
			$lead->requeue('staff idle');
		}
	}

	public function pickup($user)
	{
		$this->attributes['pickedup_by'] = $user->id;
		$this->attributes['pickedup_at'] = Carbon::now();
		$this->pickups()
			->create([
				'sales_id' => $user->id
			]);

		$this->save();
	}

	public function requeue($status, $reason = null)
	{
		// can't see that the lead is picked up, just exit
		if(!$this->relationLoaded('pickups') || empty($this->pickups)) {
			return false;
		}

		// update lead and next contact at based on requeue context
		switch($status) {
			case 'requeued':
				$this->attributes['next_contact_at'] = Carbon::now()->addHours(1);
				break;
			case 'responded':
				$this->attributes['next_contact_at'] = Carbon::now()->addDay();
				break;
			// handle pseudo statuses. These must set the $status value to a real status for lead_pickup
			case 'amendment':
				$status = 'responded';
				break;
		}
		// update status from 'new' to 'requeued'
		if($this->attributes['status'] == 'new') {
			$this->attributes['status'] = 'requeued';
		}
		$this->save();

		// requeued lead_pickup
		foreach($this->pickups as $pickup) {
			$pickup->update([
				'requeued_at'    => Carbon::now(),
				'status'         => $status,
				'requeue_reason' => $reason,
			]);
		}
		return true;
	}

	public function claim()
	{
		$user = auth()->user();

		$this->attributes['status'] = 'claimed';
		$this->attributes['claimed_by'] = $user->id;
		$this->save();

		// insert claim
		$this->claims()->create([
			'lead_id' => $this->id,
			'sales_id' => $user->id,
		]);
	}

	/// Section: RelationshipTrait

	public function queryMacros()
	{
		return [
			'status' => function($query, $value) {
				// filter by status alias
				$statusMap = $this::STATUS_ALIASES;

				// get set of status by an alias, or if not found, by name
				$value = trim(strtolower($value));
				$statuses = isset($statusMap[$value]) ? $statusMap[$value] : [$value];

				// filter by specified status
				$query->whereIn('status', $statuses);
			}
		];
	}
}
