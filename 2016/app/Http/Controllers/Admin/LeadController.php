<?php
/**
 * This file contains the LeadController Class.
 *
 * @author     Satoshi Payne <satoshi.payne@gmail.com>
 * @copyright  Copyright (c) 2016, Satoshi Payne
 */

namespace App\Http\Controllers\Admin;

// App
use App\Http\Controllers\Controller;
use App\Lead;
use App\LeadInteraction;
use App\LeadNote;
use App\LeadPickup;

// Laravel
use Illuminate\Http\Request;

/**
 * The LeadController Controller Class implements a set of routes/actions for displaying or manipulating leads.
 *
 * @author    Satoshi Payne <satoshi.payne@gmail.com>
 * @category  Controllers
 * @package   Lead
 */
class LeadController extends Controller
{
	/**
	 * GET /admin/leads
	 * ROUTE admin::lead.listing
	 *
	 * Show a list of leads. This will also handle any ajax requests for the leads table.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function listing(Request $request)
	{
		// params

		// datatable defaults for page load
		// - status [default: active]
		// - order [default: next_contact_at]
		// - paginate [default: 30]

		// searchable
		$default = ['status' => 'active'];
		$searchable = $request->input('searchable', $default);

		// orderable
		$default = ['next_contact_at' => 'asc'];
		$orderable = $request->input('orderable', $default);

		// get leads
		$leads = Lead::query()
			->select('lead.*')
			->search($searchable)
			->order($orderable)
			->paginate(3);

		// get statuses
		$statuses = collect([
			['name' => 'new'],
			['name' => 'active'],
			['name' => 'unclaimed'],
			['name' => 'converted'],
			['name' => 'inactive'],
			['name' => 'all'],
		]);

		// response
		$data = [
			'leads' => $leads,
		];
		if($request->ajax()) {
			return response()->json(collect($data));
		} else {
			$data += [
				'datatable' => with(new Lead)->newCollection(),
				'statuses' => $statuses,
			];
			return view('admin.lead.listing', $data);
		}
	}

	/**
	 * GET /admin/leads/grab
	 * ROUTE admin::lead.grab
	 *
	 * Grab a lead.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function grab(Request $request)
	{
		$user = auth()->user();

		// grab lead
		$grabbedLead = Lead::getPickedUpLeadBy($user);

		// if has grabbed lead, redirect to it
		if($grabbedLead) {
			return redirect()->route('admin::lead.details', [$grabbedLead->id]);
		}

		// refresh leads
		Lead::refreshLeads();

		// get next available lead
		$grabLead = Lead::getNextAvailableLead();

		// if lead found, redirect to it
		if($grabLead) {
			$grabLead->pickup($user);
			return redirect()->route('admin::lead.details', [$grabLead->id]);
		}

		// no lead found
		return redirect()->back()->with('error', 'No Lead');
	}

	/**
	 * GET /admin/leads/{lead}
	 * ROUTE admin::lead.details
	 *
	 * View lead details.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Lead                 $lead
	 * @return \Illuminate\Http\Response
	 */
	public function details(Request $request, Lead $lead)
	{
		// get lead interactions
		$leadInteractions = LeadInteraction::with([
			'sales',
			'notes',
		])
			->where('lead_id', '=', $lead->id)
			->get();

		// response
		$data = [
			'lead' => $lead,
			'leadInteractions' => $leadInteractions,
		];
		return view('admin.lead.details', $data);
	}

	/**
	 * GET /admin/leads/{lead}/ping
	 * ROUTE admin::lead.ping
	 *
	 * Update a lead pickup so that it doesn't get flagged as idle.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Lead                 $lead
	 * @return \Illuminate\Http\Response
	 */
	public function ping(Request $request, Lead $lead)
	{
		$user = auth()->user();

		$state = $request->input('state');

		// get open pickup
		$leadPickup = LeadPickup::query()
			->where('lead_id', '=', $lead->id)
			->where('sales_id', '=', $user->id)
			->where('status', '=', 'open')
			->first();

		// redirect depending on if lead is still open
		$redirect = true;
		if($leadPickup) {
			$redirect = false;

			// refresh open state if lead is active
			if($state == 'active') {
				$leadPickup->touch();
			}
		}

		// response
		return response()->json([
			'redirect' => $redirect
		]);
	}

	/**
	 * POST /admin/leads/{lead}/requeue
	 * ROUTE admin::lead.requeue
	 *
	 * Requeue a lead
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Lead                 $lead
	 * @return \Illuminate\Http\Response
	 */
	public function requeue(Request $request, Lead $lead)
	{
		// @note: we have to do some checks here before requeuing the lead. Let's think about this later

		// load pickups
		$lead->load([
			'pickups' => function($query) {
				$query->where('status', '=', 'open');
			}
		]);

		// requeue the lead
		$lead->requeue('requeued', $request->input('requeue_reason'));

		// response
		return redirect()->route('admin::lead.listing')->with('message', 'Lead requeued');
	}

	/**
	 * POST /admin/leads/{lead}/respond
	 * ROUTE admin::lead.respond
	 *
	 * Append internal notes to a lead interaction
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Lead                 $lead
	 * @return \Illuminate\Http\Response
	 */
	public function respond(Request $request, Lead $lead)
	{
		$user = auth()->user();

		// get previous interaction (used for determining certain logic)
		$lastInteraction = $lead->interactions()
			->orderBy('created_at', 'desc')
			->first();

		// create new outgoing interaction
		$interaction = LeadInteraction::create([
			'lead_id' => $lead->id,
			'sales_id' => $user->id,
			'context' => 'outgoing',
			'email_message' => $request->input('email_message'),
		]);

		// if present, also append internal notes to new interaction
		if($request->has('notes')) {
			LeadNote::create([
				'lead_id' => $lead->id,
				'interaction_id' => $interaction->id,
				'sales_id' => $user->id,
				'notes' => $request->input('notes'),
			]);
		}

		// if lead not claimed, make user claim it
		if(is_null($lead->claimed_by)) {
			$lead->claim();
		}

		// load pickups
		$lead->load([
			'pickups' => function($query) {
				$query->where('status', '=', 'open');
			}
		]);

		// get respond state
		$status = 'responded';

		// if the current interaction is a response to the lead or an amendment
		if(!is_null($lastInteraction) && $lastInteraction->context == 'outgoing' && $interaction->context == 'outgoing') {
			$status = 'amendment';
		}

		// requeue to complete pickup
		$lead->requeue($status);

		// response
		return redirect()->route('admin::lead.listing');
	}

	/**
	 * POST /admin/leads/{lead}/interactions/{lead_interaction}/notes
	 * ROUTE admin::lead.notes
	 *
	 * Append internal notes to a lead interaction
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Lead                 $lead
	 * @param  \App\LeadInteraction      $leadInteraction
	 * @return \Illuminate\Http\Response
	 */
	public function notes(Request $request, Lead $lead, LeadInteraction $leadInteraction)
	{
		$user = auth()->user();

		// append internal notes to interaction
		LeadNote::create([
			'lead_id' => $lead->id,
			'interaction_id' => $leadInteraction->id,
			'sales_id' => $user->id,
			'notes' => $request->input('notes'),
		]);
		return redirect()->route('admin::lead.listing')->with('message', 'notes saved');
	}
}
