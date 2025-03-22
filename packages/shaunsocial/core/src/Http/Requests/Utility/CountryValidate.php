<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Traits\RuntimeFormRequest;

class CountryValidate extends BaseFormRequest
{
    use RuntimeFormRequest;
    
    public function rules()
    {
        return [
            'country_id' => [
                'nullable',
                function ($attribute, $countryId, $fail) {
                    if ($countryId) {
                        $country = Country::findByField('id', $countryId);
                        if (! $country || ! $country->is_active) {
                            return $fail(__('The country is not found.'));
                        }
                    }
                },
            ],
            'state_id' => [
                'nullable',
                function ($attribute, $stateId, $fail) {
                    if ($stateId) {
                        $state = State::findByField('id', $stateId);
                        if (! $state || ! $state->is_active) {
                            return $fail(__('The state is not found.'));
                        }
                    }
                },
            ],
            'city_id' => [
                'nullable',
                function ($attribute, $cityId, $fail) {
                    if ($cityId) {
                        $city = City::findByField('id', $cityId);
                        if (! $city || ! $city->is_active) {
                            return $fail(__('The city is not found.'));
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                if (! empty($data['country_id'])) {
                    if (! empty($data['state_id'])) {
                        $state = State::findByField('id', $data['state_id']);
                        if ($data['country_id'] != $state->country_id) {
                            return $validator->errors()->add('state_id', __('The state is not found.'));
                        }

                        if (! empty($data['city_id'])) {
                            $city = City::findByField('id', $data['city_id']);
                            if ($state->id != $city->state_id) {
                                return $validator->errors()->add('state_id', __('The city is not found.'));
                            }
                        }
                    } else {
                        if (! empty($data['city_id'])) {
                            return $validator->errors()->add('city_id', __('The city id must be empty.'));
                        }
                    }
                } else {
                    if (! empty($data['state_id'])) {
                        return $validator->errors()->add('state_id', __('The state id must be empty.'));
                    }

                    if (! empty($data['city_id'])) {
                        return $validator->errors()->add('city_id', __('The city id must be empty.'));
                    }
                }
            });
        }

        return $validator;
    }
}
