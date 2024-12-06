<?php

namespace App\Http\Controllers;

use App\Category;
use App\Countries;
use App\CountryDetails;
use App\EmailListContent;
use App\Faq;
use App\Item;
use App\ProhibitedItem;
use App\Seller;
use App\Status;
use File;
use App\StoreCategory;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Str;
use Spatie\TranslationLoader\LanguageLine;

class ModeratorController extends HomeController
{
	public function index()
	{
		$country = Countries::all();

		return view('backend.moderator.index')->with('country', $country);
	}

	/*-------------------  FAQ   -------------------- */
	public function showFAQ()
	{
		return Faq::paginate(10);
	}

	public function createFAQ(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'answer_az' => ['required', 'string'],
					'answer_ru' => ['required', 'string'],
					'answer_en' => ['required', 'string'],
					'question_az' => ['required', 'string'],
					'question_ru' => ['required', 'string'],
					'question_en' => ['required', 'string'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$newFAQ = Faq::firstOrCreate($request->all());
			if ($newFAQ->wasRecentlyCreated) {
				return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New FAQ was successfully created']);
			} else {
				return response(['case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This faq already exists']);
			}
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function updateFAQ(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'id' => ['required', 'integer'],
					'answer_az' => ['required', 'string'],
					'answer_ru' => ['required', 'string'],
					'answer_en' => ['required', 'string'],
					'question_az' => ['required', 'string'],
					'question_ru' => ['required', 'string'],
					'question_en' => ['required', 'string'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editFAQ = Faq::findOrFail($request->input('id'));
			$editFAQ->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The FAQ was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteFAQ(Faq $faq)
	{
		try {
			$deletedFAQ = Faq::findOrFail($faq->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The FAQ was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	/*-------------------  ProhibitedItem   -------------------- */
	public function showProhibitedItem()
	{
		return ProhibitedItem::with('country')->paginate(10);
	}

	public function createProhibitedItem(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'item_az' => ['required', 'string'],
					'item_ru' => ['required', 'string'],
					'item_en' => ['required', 'string'],
					'country_id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$newProhibitedItem = ProhibitedItem::firstOrCreate($request->all());
			if ($newProhibitedItem->wasRecentlyCreated) {
				return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New Prohibited Item was successfully created']);
			} else {
				return response(['case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This Prohibited Item already exists']);
			}
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function updateProhibitedItem(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'item_az' => ['required', 'string'],
					'item_ru' => ['required', 'string'],
					'item_en' => ['required', 'string'],
					'country_id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editFAQ = ProhibitedItem::findOrFail($request->input('id'));
			$editFAQ->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Prohibited Item was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteProhibitedItem(ProhibitedItem $item)
	{
		try {
			$deletedFAQ = ProhibitedItem::findOrFail($item->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The Prohibited Item was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	/*-------------------  Store   -------------------- */
	public function showStore(Request $request)
	{
		return Seller::with('country')->with('category')->where('name', 'LIKE', '%' . $request->name . '%')->where('seller.only_collector', 0)->paginate(10);
	}

	public function createStore(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'title' => ['required', 'string', 'max:255'],
					'name' => ['required', 'string', 'max:255'],
					'url' => ['required', 'nullable'],
					'country_id.*' => ['required', 'integer'],
					'category_id.*' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}

			$name = $request->input('name');
			$name = Str::slug($name);

			if (Seller::where('name', $name)->select('id')->first()) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Seller exists!']);
			}

			$request->merge(['created_by' => Auth::id(), 'name' => $name]);

			$newStore = Seller::firstOrCreate($request->except(['category_id', 'country_id', 'file', 'url']));
			if (!empty($request->file('file')) || $request->file('file') !== null || trim($request->file('file')) !== '') {
				$image_name = 'store-' . trim(Str::random(4) . str_replace(' ', '', microtime()));
				Storage::disk('uploads')->makeDirectory('images/works');
				$cover = $request->file('file');
				$extension = $cover->getClientOriginalExtension();
				Storage::disk('uploads')->put('images/stores/' . $image_name . '.' . $extension, File::get($cover));
				$image_address = '/uploads/images/stores/' . $image_name . '.' . $extension;
				$newStore->img = $image_address;
			}
			$newStore->url = 'http://colibr.link/r?url=' . $request->input('url');
			$newStore->country()->sync(json_decode($request->input('country_id'), true));
			$newStore->category()->sync(json_decode($request->input('category_id'), true));
			$newStore->save();
			if ($newStore->wasRecentlyCreated) {
				return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New Prohibited Item was successfully created', 'data' => $newStore]);
			} else {
				return response(['case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This Prohibited Item already exists']);
			}
			//         return response([ 'case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Prohibited Item was successfully updated' ]);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function updateStore(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'title' => ['required', 'string', 'max:255'],
					'name' => ['required', 'string', 'max:255'],
					'url' => ['required', 'nullable'],
					'country_id.*' => ['required', 'integer'],
					'category_id.*' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}

			$name = $request->input('name');
			$name = Str::slug($name);

			if (Seller::where('name', $name)->where('id', '<>', $request->input('id'))->select('id')->first()) {
				return response(['case' => 'warning', 'title' => 'Oops!', 'content' => 'Seller exists!']);
			}

			$request->merge(['name' => $name]);

			$editStore = Seller::findOrFail($request->input('id'));
			$editStore->name = $request->input('name');
			$editStore->title = $request->input('title');
			$editStore->url = $request->input('url');
			if (!empty($request->file('file')) || $request->file('file') !== null || trim($request->file('file')) !== '') {
				$image_name = 'store-' . trim(Str::random(4) . str_replace(' ', '', microtime()));
				Storage::disk('uploads')->makeDirectory('images/works');
				$cover = $request->file('file');
				$extension = $cover->getClientOriginalExtension();
				Storage::disk('uploads')->put('images/stores/' . $image_name . '.' . $extension, File::get($cover));
				$image_address = '/uploads/images/stores/' . $image_name . '.' . $extension;
				$editStore->img = $image_address;
			}
			$editStore->save();
			$editStore->country()->sync(json_decode($request->input('country_id'), true));
			$editStore->category()->sync(json_decode($request->input('category_id'), true));

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Prohibited Item was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteStore(Seller $item)
	{
		try {
			$deletedFAQ = Seller::findOrFail($item->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The Seller was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function getCountries(Seller $item)
	{
		return Countries::all();
	}

	public function getCategories(Seller $item)
	{
		return StoreCategory::all();
	}

	public function changeCheck(Request $request, Seller $item)
	{

		try {
			$validator = Validator::make($request->all(), [
					'val' => ['required', 'boolean'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$item->has_site = $request->val;
			$item->save();

			return response(['case' => 'success', 'title' => 'Item was successfully changed', 'content' => 'The Seller was successfully changed (has_site)']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	/*-------------------  StoreCategory   -------------------- */
	public function showStoreCategory(Request $request)
	{
		return StoreCategory::where('name_az', 'LIKE', '%' . $request->name . '%')->paginate(10);
	}

	public function createStoreCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'name_az' => ['required', 'string'],
					'name_en' => ['required', 'string'],
					'name_ru' => ['required', 'string'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$newStore = StoreCategory::firstOrCreate(['name_az' => $request->input('name_az'), 'created_by' => Auth::user()->id], $request->all());
			if ($newStore->wasRecentlyCreated) {
				return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New Store Category was successfully created', 'data' => $newStore]);
			} else {
				return response(['case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This Store Category already exists']);
			}
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function updateStoreCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'name_az' => ['required', 'string'],
					'name_en' => ['required', 'string'],
					'name_ru' => ['required', 'string'],
					'id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editStoreCategory = StoreCategory::findOrFail($request->input('id'));
			$editStoreCategory->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Store Category was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteStoreCategory(StoreCategory $item)
	{
		try {
			$deletedFAQ = StoreCategory::findOrFail($item->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The Store Category was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	/*-------------------  CountryDetails   -------------------- */
	public function showCountryDetails(Request $request)
	{
		return CountryDetails::with((array('country' => function ($query) {
			$query->select('id', 'name_az');
		})))->select('country_id')->groupBy('country_id')->paginate(10);
	}

	public function selectCountryDetails($id)
	{
		return CountryDetails::where('country_id', '=', $id)->get();
	}

	public function updateCountryDetails(Request $request)
	{
		try {
			/*$validator = Validator ::make($request -> all(), [
				 'name' => [ 'required', 'string' ],
			]);
			if ( $validator -> fails() ) {
				 return response([ 'case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator -> errors() -> toArray() ]);
			}*/
			/*$newStore = CountryDetails ::firstOrCreate([ 'name' => $request -> data]);
			if ( $newStore -> wasRecentlyCreated ) {
				 return response([ 'case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New Store Category was successfully created', 'data' => $newStore ]);
			}
			else {
				 return response([ 'case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This Store Category already exists' ]);
			}*/
			$data = $request->all();
			for ($i = 0; $i < count($data); $i++) {
				$newCountryDetails = CountryDetails::firstOrCreate(['id' => ($data[$i]['id'] ?? null)]);
				$newCountryDetails->country_id = $data[$i]['country_id'];
				$newCountryDetails->title = $data[$i]['title'];
				$newCountryDetails->information = $data[$i]['information'];
				$newCountryDetails->save();
			}

			return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'Country Details was successfully changed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteCountryDetails(CountryDetails $item)
	{
		try {
			$deletedFAQ = CountryDetails::findOrFail($item->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The Store Category was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}


	/*-------------------  MailSMSTemplate   -------------------- */
	public function showMailSMSTemplate()
	{
		return EmailListContent::paginate(10);
	}

	public function updateMailSMSTemplate(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'id' => ['required', 'integer'],
					'title_az' => ['required', 'string'],
					'title_ru' => ['required', 'string'],
					'title_en' => ['required', 'string'],
					'subject_az' => ['required', 'string'],
					'subject_ru' => ['required', 'string'],
					'subject_en' => ['required', 'string'],
					'content_az' => ['required', 'string'],
					'content_ru' => ['required', 'string'],
					'content_en' => ['required', 'string'],
					'list_inside_az' => ['nullable', 'string'],
					'list_inside_ru' => ['nullable', 'string'],
					'list_inside_en' => ['nullable', 'string'],
					'content_bottom_az' => ['required', 'string'],
					'content_bottom_ru' => ['required', 'string'],
					'content_bottom_en' => ['required', 'string'],
					'button_name_az' => ['nullable', 'string'],
					'button_name_ru' => ['nullable', 'string'],
					'button_name_en' => ['nullable', 'string'],
					'sms_az' => ['nullable', 'string'],
					'sms_ru' => ['nullable', 'string'],
					'sms_en' => ['nullable', 'string'],
					'push_content_az' => ['nullable', 'string'],
					'push_content_ru' => ['nullable', 'string'],
					'push_content_en' => ['nullable', 'string']
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editFAQ = EmailListContent::findOrFail($request->input('id'));
			$editFAQ->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Email/SMS Template was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}


	/*-------------------  Category   -------------------- */
	public function showCategory(Request $request)
	{
		return Category::all();
	}

	public function createCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'name_az' => ['required', 'string'],
					'name_en' => ['required', 'string'],
					'name_ru' => ['required', 'string'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$newStore = Category::firstOrCreate(['name_az' => $request->input('name_az'), 'created_by' => Auth::user()->id], $request->all());
			if ($newStore->wasRecentlyCreated) {
				return response(['case' => 'success', 'title' => 'Item was successfully created', 'content' => 'New Store Category was successfully created', 'data' => $newStore]);
			} else {
				return response(['case' => 'error', 'title' => 'Already exists', 'type' => 'firstOrCreate', 'content' => 'This Store Category already exists']);
			}
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function updateCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'name_az' => ['required', 'string'],
					'name_en' => ['required', 'string'],
					'name_ru' => ['required', 'string'],
					'id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editStoreCategory = Category::findOrFail($request->input('id'));
			$editStoreCategory->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Store Category was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function mergeCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'category_id' => ['required', 'integer'],
					'id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}


			try {
				$item = Item::where('category_id', $request->input('id'))->firstOrFail();
				$item->update(['category_id' => $request->input('category_id')]);
				$deletedFAQ = Category::findOrFail($request->input('id'));
				$deletedFAQ->deleted_by = Auth::user()->id;
				$deletedFAQ->deleted_at = now();
				$deletedFAQ->save();
				return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Category was successfully merged']);
			} catch (ModelNotFoundException $e) {
				$deletedFAQ = Category::findOrFail($request->input('id'));
				$deletedFAQ->deleted_by = Auth::user()->id;
				$deletedFAQ->deleted_at = now();
				$deletedFAQ->save();
				return response(['case' => 'success', 'title' => 'Item was successfully Merged/Deleted', 'content' => 'There is no item with this category']);
			}
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function deleteCategory(Category $item)
	{
		try {
			$deletedFAQ = Category::findOrFail($item->id);
			$deletedFAQ->deleted_by = Auth::user()->id;
			$deletedFAQ->deleted_at = now();
			$deletedFAQ->save();

			return response(['case' => 'success', 'title' => 'Item was successfully removed', 'content' => 'The Store Category was successfully removed']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	/*-------------------  Language   -------------------- */
	public function showLanguageTemplate(Request $request)
	{
		return LanguageLine::where(['group' => $request->lang])
				->paginate(10);
	}

	public function updateLanguageMenu(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'key' => ['required', 'string'],
					'group' => ['required', 'string'],
					'lang.*' => ['required', 'string'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			LanguageLine::where(['group' => $request->input('group'), 'key' => $request->input('key')])
					->update([
							'text' => $request->input('lang'),
					]);

			$operator = new OperatorUserController();

			$get_token = $operator->get_token_for_login_api(0);

			if (!$get_token[0]) {
				return response(['case' => 'error', 'title' => 'Error!', 'content' => 'Sorry, an error has occurred!']);
			}

			$token = $get_token[1];

			$url = 'https://asercargo.az/secret/backend/cache-clear?';
			$url .= 'token=' . $token;

			$client = new Client();
			$response = $client->get($url);
			$api_status = $response->getStatusCode();

			if ($api_status != 200) {
				return response(['case' => 'success', 'title' => 'Oops!', 'content' => 'The Lang was successfully updated. However, the changes have not been updated!']);
			}

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Lang was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}

	public function showLanguageSidebar(Request $request)
	{
		return LanguageLine::select('group')->distinct()->get();
	}


	/*-------------------  Status   -------------------- */
	public function showStatus(Request $request)
	{
		return Status::paginate(5);
	}

	public function updateStatus(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
					'status_az' => ['required', 'string'],
					'status_en' => ['required', 'string'],
					'status_ru' => ['required', 'string'],
					'id' => ['required', 'integer'],
			]);
			if ($validator->fails()) {
				return response(['case' => 'warning', 'title' => 'Warning!', 'type' => 'validation', 'content' => $validator->errors()->toArray()]);
			}
			$editStoreCategory = Status::findOrFail($request->input('id'));
			$editStoreCategory->update($request->all());

			return response(['case' => 'success', 'title' => 'Item was successfully updated', 'content' => 'The Status was successfully updated']);
		} catch (Exception $e) {
			return response(['case' => 'error', 'title' => 'Warning!', 'type' => 'try/catch', 'content' => $e->getMessage()]);
		}
	}
}
