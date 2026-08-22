<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GeographySeeder extends Seeder
{
    public function run(): void
    {
        $geographyData = [
            [
                'name' => 'United Arab Emirates',
                'iso2' => 'AE',
                'iso3' => 'ARE',
                'phone_code' => '971',
                'currency_code' => 'AED',
                'flag' => '🇦🇪',
                'states' => [
                    [
                        'name' => 'Abu Dhabi',
                        'code' => 'AZ',
                        'cities' => ['Abu Dhabi City', 'Al Ain', 'Al Dhafra']
                    ],
                    [
                        'name' => 'Dubai',
                        'code' => 'DU',
                        'cities' => ['Dubai City', 'Jebel Ali', 'Hatta']
                    ],
                    [
                        'name' => 'Sharjah',
                        'code' => 'SH',
                        'cities' => ['Sharjah City', 'Khor Fakkan', 'Kalba']
                    ],
                    [
                        'name' => 'Ajman',
                        'code' => 'AJ',
                        'cities' => ['Ajman City', 'Manama', 'Masfout']
                    ],
                    [
                        'name' => 'Ras Al Khaimah',
                        'code' => 'RK',
                        'cities' => ['Ras Al Khaimah City', 'Al Rams', 'Digdaga']
                    ],
                    [
                        'name' => 'Fujairah',
                        'code' => 'FU',
                        'cities' => ['Fujairah City', 'Dibba Al-Fujairah']
                    ],
                    [
                        'name' => 'Umm Al Quwain',
                        'code' => 'UQ',
                        'cities' => ['Umm Al Quwain City', 'Falaj Al Mualla']
                    ],
                ]
            ],
            [
                'name' => 'Saudi Arabia',
                'iso2' => 'SA',
                'iso3' => 'SAU',
                'phone_code' => '966',
                'currency_code' => 'SAR',
                'flag' => '🇸🇦',
                'states' => [
                    [
                        'name' => 'Riyadh Province',
                        'code' => '01',
                        'cities' => ['Riyadh', 'Al Kharj', 'Diriyah']
                    ],
                    [
                        'name' => 'Makkah Province',
                        'code' => '02',
                        'cities' => ['Jeddah', 'Mecca', 'Taif']
                    ],
                    [
                        'name' => 'Eastern Province',
                        'code' => '04',
                        'cities' => ['Dammam', 'Khobar', 'Jubail', 'Dhahran']
                    ],
                    [
                        'name' => 'Madinah Province',
                        'code' => '03',
                        'cities' => ['Medina', 'Yanbu']
                    ],
                    [
                        'name' => 'Asir Province',
                        'code' => '14',
                        'cities' => ['Abha', 'Khamis Mushait']
                    ],
                    [
                        'name' => 'Tabuk Province',
                        'code' => '07',
                        'cities' => ['Tabuk', 'Neom']
                    ],
                ]
            ],
            [
                'name' => 'Qatar',
                'iso2' => 'QA',
                'iso3' => 'QAT',
                'phone_code' => '974',
                'currency_code' => 'QAR',
                'flag' => '🇶🇦',
                'states' => [
                    [
                        'name' => 'Ad Dawhah',
                        'code' => 'DA',
                        'cities' => ['Doha', 'The Pearl-Qatar', 'West Bay']
                    ],
                    [
                        'name' => 'Al Rayyan',
                        'code' => 'RA',
                        'cities' => ['Al Rayyan City', 'Education City', 'Lusail']
                    ],
                    [
                        'name' => 'Al Wakrah',
                        'code' => 'WA',
                        'cities' => ['Al Wakrah City', 'Mesaieed']
                    ],
                    [
                        'name' => 'Al Khor',
                        'code' => 'KH',
                        'cities' => ['Al Khor City', 'Al Thakhira']
                    ],
                ]
            ],
            [
                'name' => 'Oman',
                'iso2' => 'OM',
                'iso3' => 'OMN',
                'phone_code' => '968',
                'currency_code' => 'OMR',
                'flag' => '🇴🇲',
                'states' => [
                    [
                        'name' => 'Muscat Governorate',
                        'code' => 'MA',
                        'cities' => ['Muscat', 'Seeb', 'Muttrah', 'Bawshar']
                    ],
                    [
                        'name' => 'Dhofar Governorate',
                        'code' => 'DA',
                        'cities' => ['Salalah', 'Taqah', 'Mirbat']
                    ],
                    [
                        'name' => 'Al Batinah North',
                        'code' => 'BS',
                        'cities' => ['Sohar', 'Saham', 'Shinaz']
                    ],
                    [
                        'name' => 'Ad Dakhiliyah',
                        'code' => 'DAK',
                        'cities' => ['Nizwa', 'Bahla', 'Izki']
                    ],
                ]
            ],
            [
                'name' => 'Bahrain',
                'iso2' => 'BH',
                'iso3' => 'BHR',
                'phone_code' => '973',
                'currency_code' => 'BHD',
                'flag' => '🇧🇭',
                'states' => [
                    [
                        'name' => 'Capital Governorate',
                        'code' => '13',
                        'cities' => ['Manama', 'Juffair', 'Seef']
                    ],
                    [
                        'name' => 'Muharraq Governorate',
                        'code' => '15',
                        'cities' => ['Muharraq', 'Amwaj Islands', 'Busaiteen']
                    ],
                    [
                        'name' => 'Northern Governorate',
                        'code' => '17',
                        'cities' => ['Budaiya', 'Saar', 'Diraz']
                    ],
                    [
                        'name' => 'Southern Governorate',
                        'code' => '14',
                        'cities' => ['Riffa', 'Isa Town', 'Zallaq']
                    ],
                ]
            ],
            [
                'name' => 'Kuwait',
                'iso2' => 'KW',
                'iso3' => 'KWT',
                'phone_code' => '965',
                'currency_code' => 'KWD',
                'flag' => '🇰🇼',
                'states' => [
                    [
                        'name' => 'Al Asimah (Capital)',
                        'code' => 'KU',
                        'cities' => ['Kuwait City', 'Dasman', 'Sharq']
                    ],
                    [
                        'name' => 'Hawalli Governorate',
                        'code' => 'HA',
                        'cities' => ['Hawally', 'Salmiya', 'Jabriya']
                    ],
                    [
                        'name' => 'Al Farwaniyah',
                        'code' => 'FA',
                        'cities' => ['Farwaniya', 'Khaitan', 'Jleeb Al-Shuyoukh']
                    ],
                    [
                        'name' => 'Al Jahra Governorate',
                        'code' => 'JA',
                        'cities' => ['Al Jahra', 'Sulaibiya']
                    ],
                ]
            ],
            [
                'name' => 'Uzbekistan',
                'iso2' => 'UZ',
                'iso3' => 'UZB',
                'phone_code' => '998',
                'currency_code' => 'UZS',
                'flag' => '🇺🇿',
                'states' => [
                    [
                        'name' => 'Tashkent City / Region',
                        'code' => 'TK',
                        'cities' => ['Tashkent', 'Chirchiq', 'Angren', 'Olmaliq']
                    ],
                    [
                        'name' => 'Samarkand Region',
                        'code' => 'SA',
                        'cities' => ['Samarkand', 'Kattakurgan', 'Urgut']
                    ],
                    [
                        'name' => 'Bukhara Region',
                        'code' => 'BU',
                        'cities' => ['Bukhara', 'Gijduvan', 'Kagan']
                    ],
                    [
                        'name' => 'Fergana Region',
                        'code' => 'FA',
                        'cities' => ['Fergana', 'Kokand', 'Margilan']
                    ],
                    [
                        'name' => 'Andijan Region',
                        'code' => 'AN',
                        'cities' => ['Andijan', 'Asaka', 'Shahrixon']
                    ],
                ]
            ],
            [
                'name' => 'Bangladesh',
                'iso2' => 'BD',
                'iso3' => 'BGD',
                'phone_code' => '880',
                'currency_code' => 'BDT',
                'flag' => '🇧🇩',
                'states' => [
                    [
                        'name' => 'Dhaka Division',
                        'code' => '13',
                        'cities' => ['Dhaka', 'Gazipur', 'Narayanganj', 'Faridpur']
                    ],
                    [
                        'name' => 'Chittagong Division',
                        'code' => '15',
                        'cities' => ['Chittagong', 'Cox\'s Bazar', 'Comilla', 'Noakhali']
                    ],
                    [
                        'name' => 'Sylhet Division',
                        'code' => '60',
                        'cities' => ['Sylhet', 'Moulvibazar', 'Habiganj', 'Sunamganj']
                    ],
                    [
                        'name' => 'Khulna Division',
                        'code' => '27',
                        'cities' => ['Khulna', 'Jessore', 'Kushtia', 'Bagerhat']
                    ],
                    [
                        'name' => 'Rajshahi Division',
                        'code' => '54',
                        'cities' => ['Rajshahi', 'Bogra', 'Pabna', 'Naogaon']
                    ],
                    [
                        'name' => 'Barisal Division',
                        'code' => '06',
                        'cities' => ['Barisal', 'Bhola', 'Patuakhali']
                    ],
                    [
                        'name' => 'Rangpur Division',
                        'code' => '55',
                        'cities' => ['Rangpur', 'Dinajpur', 'Saidpur']
                    ],
                    [
                        'name' => 'Mymensingh Division',
                        'code' => '45',
                        'cities' => ['Mymensingh', 'Jamalpur', 'Netrokona']
                    ],
                ]
            ],
            [
                'name' => 'India',
                'iso2' => 'IN',
                'iso3' => 'IND',
                'phone_code' => '91',
                'currency_code' => 'INR',
                'flag' => '🇮🇳',
                'states' => [
                    [
                        'name' => 'Maharashtra',
                        'code' => 'MH',
                        'cities' => ['Mumbai', 'Pune', 'Nagpur', 'Thane', 'Nashik']
                    ],
                    [
                        'name' => 'Delhi NCR',
                        'code' => 'DL',
                        'cities' => ['New Delhi', 'Noida', 'Gurugram', 'Faridabad']
                    ],
                    [
                        'name' => 'Karnataka',
                        'code' => 'KA',
                        'cities' => ['Bangalore', 'Mysore', 'Hubli', 'Mangalore']
                    ],
                    [
                        'name' => 'Telangana',
                        'code' => 'TG',
                        'cities' => ['Hyderabad', 'Warangal', 'Nizamabad']
                    ],
                    [
                        'name' => 'Gujarat',
                        'code' => 'GJ',
                        'cities' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot']
                    ],
                    [
                        'name' => 'Tamil Nadu',
                        'code' => 'TN',
                        'cities' => ['Chennai', 'Coimbatore', 'Madurai', 'Salem']
                    ],
                    [
                        'name' => 'West Bengal',
                        'code' => 'WB',
                        'cities' => ['Kolkata', 'Howrah', 'Siliguri', 'Durgapur']
                    ],
                ]
            ],
            [
                'name' => 'United Kingdom',
                'iso2' => 'GB',
                'iso3' => 'GBR',
                'phone_code' => '44',
                'currency_code' => 'GBP',
                'flag' => '🇬🇧',
                'states' => [
                    [
                        'name' => 'Greater London',
                        'code' => 'LDN',
                        'cities' => ['London', 'Westminster', 'Croydon']
                    ],
                    [
                        'name' => 'Greater Manchester',
                        'code' => 'MAN',
                        'cities' => ['Manchester', 'Salford', 'Bolton']
                    ],
                    [
                        'name' => 'West Midlands',
                        'code' => 'WMD',
                        'cities' => ['Birmingham', 'Coventry', 'Wolverhampton']
                    ],
                    [
                        'name' => 'Scotland',
                        'code' => 'SCT',
                        'cities' => ['Edinburgh', 'Glasgow', 'Aberdeen', 'Dundee']
                    ],
                    [
                        'name' => 'Wales',
                        'code' => 'WLS',
                        'cities' => ['Cardiff', 'Swansea', 'Newport']
                    ],
                ]
            ],
            [
                'name' => 'United States',
                'iso2' => 'US',
                'iso3' => 'USA',
                'phone_code' => '1',
                'currency_code' => 'USD',
                'flag' => '🇺🇸',
                'states' => [
                    [
                        'name' => 'New York',
                        'code' => 'NY',
                        'cities' => ['New York City', 'Buffalo', 'Rochester', 'Albany']
                    ],
                    [
                        'name' => 'California',
                        'code' => 'CA',
                        'cities' => ['Los Angeles', 'San Francisco', 'San Diego', 'San Jose', 'Sacramento']
                    ],
                    [
                        'name' => 'Texas',
                        'code' => 'TX',
                        'cities' => ['Houston', 'Dallas', 'Austin', 'San Antonio', 'Fort Worth']
                    ],
                    [
                        'name' => 'Illinois',
                        'code' => 'IL',
                        'cities' => ['Chicago', 'Aurora', 'Naperville', 'Springfield']
                    ],
                    [
                        'name' => 'Florida',
                        'code' => 'FL',
                        'cities' => ['Miami', 'Orlando', 'Tampa', 'Jacksonville']
                    ],
                ]
            ],
        ];

        foreach ($geographyData as $cData) {
            $statesData = $cData['states'];
            unset($cData['states']);

            $country = Country::updateOrCreate(
                ['iso2' => $cData['iso2']],
                $cData
            );

            foreach ($statesData as $sIndex => $sData) {
                $citiesData = $sData['cities'];
                unset($sData['cities']);

                $state = State::updateOrCreate(
                    ['country_id' => $country->id, 'name' => $sData['name']],
                    array_merge($sData, [
                        'code'       => $sData['code'] ?? null,
                        'sort_order' => ($sIndex + 1) * 10,
                        'is_active'  => true,
                    ])
                );

                foreach ($citiesData as $cIndex => $cityName) {
                    City::updateOrCreate(
                        ['country_id' => $country->id, 'name' => $cityName],
                        [
                            'state_id'   => $state->id,
                            'sort_order' => ($cIndex + 1) * 10,
                            'is_active'  => true,
                        ]
                    );
                }
            }
        }
    }
}
