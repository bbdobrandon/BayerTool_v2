<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Redirect;
use DB;
use Illuminate\Support\Facades\Mail;

class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    public function crop(Request $request)
    {
        // $zipcode = $request->input('zipcode');
        // $zipcode = explode('-',$zipcode)[0];

        $crop_options = [];

        // if (empty($zipcode) || !preg_match('/^\d{5}$/', $zipcode)) {
        //     // invalid zip code
        //     return Redirect::to('/');
        // }

        // $zipcode = (int)$zipcode;

        // look up region from zip code
        // $row = DB::table('zipcode_regions')
        //     ->select('region')
        //     ->where('zipcode', $zipcode)
        //     ->first();

        // if (empty($row) || empty($row->region)) {
        //     // No recommendations
        //     return Redirect::to('/result?' . http_build_query(['region' => '', 'crop' => '', 'pass' => '', 'section' => '']));
        // }
        // $region = $row->region;
        
        $region = $request->input('region');

        // parse crops for region
        if ($region === 'Far West') {

            // No recommendations
            return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => '', 'pass' => '', 'section' => '']));

        } elseif ($region === 'Northeast') {

            $crop_options = ['soy','corn','wheat'];

        } elseif ($region === 'Southeast') {

            $crop_options = ['soy','corn','cotton','wheat'];

        } elseif ($region === 'Midwest') {

            $crop_options = ['soy','corn','wheat'];

        } elseif ($region === 'Western Plains') {

            $crop_options = ['soy','corn','sorghum','wheat'];

        } elseif ($region === 'Northern Plains') {

            $crop_options = ['soy','corn','sorghum','wheat'];

        } elseif ($region === 'Midsouth') {

            $crop_options = ['soy','corn','cotton','sorghum','wheat'];

        } elseif ($region === 'Southern Plains') {

            $crop_options = ['soy','corn','cotton','sorghum','wheat'];

        } else {
            // invalid region
            return Redirect::to('/');
        }

        return view('crop', [
            'region' => $region,
            'crop_options' => $crop_options,
        ]);
    }

    public function pass(Request $request)
    {
        if (empty($request->input('region')) || empty($request->input('crop'))) {
            // invalid region
            return Redirect::to('/');
        }
        $region = $request->input('region');
        $crop = $request->input('crop');

        // parse passes for region+crop
        if ($crop !== 'corn' && ($crop !== 'wheat' || $region !== 'Northern Plains')) {
            if ($crop === 'wheat') {
                $pass = 'Winter Wheat';
            } else {
                $pass = '';
            }
            return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => '']));
        }

        if ($crop === 'corn') {
            $pass_options = [1,2];
        } elseif ($crop === 'wheat') {
            $pass_options = ['winter wheat','spring wheat'];
        }

        return view('pass', [
            'region' => $region,
            'crop' => $crop,
            'pass_options' => $pass_options,
        ]);
    }

    public function section(Request $request)
    {
        if (empty($request->input('region')) || empty($request->input('crop')) || empty($request->input('pass'))) {
            // invalid region
            return Redirect::to('/');
        }
        $region = $request->input('region');
        $crop = $request->input('crop');
        $pass = $request->input('pass');

        // parse sections for region + pass
        if (!in_array($crop,['corn','wheat'])) {
            // No recommendations
            return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => '']));
        } elseif ($crop === 'wheat') {
            return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => '']));
        } elseif ($crop === 'corn') {
            if ($pass == 1) {
                $sections = ['Pre','Post'];
            } else {
                return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => '']));
            }
        }

        return view('section', [
            'region' => $region,
            'crop' => $crop,
            'pass' => $pass,
            'section_options' => $sections,
        ]);
    }

    public function getRecommendation($region = null, $crop = null, $pass = null, $section = null)
    {
        // unknown selection
        $recommendation = false;

        if (!empty($region) && $region !== 'Far West') {
            if ($region === 'Northeast') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Harness® MAX herbicide',
                                            'Atrazine',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Harness® MAX herbicide',
                                            'Atrazine',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Harness® MAX herbicide',
                                        'Atrazine',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'Capreno® herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                            'process' => [
                                'Burndown' => [
                                    'Herbicides' => [
                                        'Roundup PowerMAX® herbicide',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Osprey® herbicide',
                                    ],
                                ],
                                '' => [
                                    'Fungicides' => [
                                        'Prosaro® fungicide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                }
            } elseif ($region === 'Southeast') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH'
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Balance® Flexx herbicide',
                                            ['Harness® Xtra Herbicide','Harness® Xtra 5.6L Herbicide'],
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Harness® MAX herbicide',
                                            'Atrazine',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Harness® MAX herbicide',
                                        'Atrazine',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'DiFlexx® DUO herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'cotton') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    '2,4-D or dicamba',
                                ],
                            ],
                            'Pre' => [
                                'Herbicides' => [
                                    'Paraquat',
                                    'Warrant® Herbicide',
                                    'Diuron',
                                ],
                                'Insecticides/Nematicides' => [
                                    'Velum® Total insecticide/nematicide',
                                ],
                            ],
                            'Post 1' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                ],
                                'Plant Growth Regulator' => [
                                    'Stance® 110 SC plant regulator',
                                ],
                            ],
                            'Post 2*' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Proline® fungicide',
                                ],
                                'Insecticides' => [
                                    'Sivanto™ Prime insecticide',
                                ],
                            ],
                            'Lay-By' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'Diuron or MSMA',
                                ],
                            ],
                            'Pre-Harvest' => [
                                'Defoliants' => [
                                    'Ginstar® EC cotton defoliant',
                                    'Finish® 6 PRO cotton defoliant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Bayer recommends two post over-the-top herbicide applications in Cotton',
                        ],
                    ];
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup PowerMAX® herbicide',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Osprey® herbicide',
                                ],
                            ],
                            '' => [
                                'Fungicides' => [
                                    'Prosaro® fungicide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => false,
                    ];
                }
            } elseif ($region === 'Midwest') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide***'],
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide'],
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH',
                            '***If you use Warrant® Ultra in a pre-emergence application, Warrant® must be used in the post-emergence application',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Corvus® herbicide',
                                            ['Harness® Xtra Herbicide','Harness® Xtra 5.6L Herbicide'],
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Capreno® herbicide',
                                            'Degree® Xtra herbicide',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Corvus® herbicide',
                                        'Atrazine',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'Harness® MAX herbicide',
                                        'Atrazine',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup PowerMAX® herbicide',
                                    'RT3® brand herbicide',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Huskie® Complete herbicide',
                                ],
                            ],
                            '' => [
                                'Fungicides' => [
                                    'Prosaro® fungicide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => false,
                    ];
                }
            } elseif ($region === 'Western Plains') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide'],
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Corvus® herbicide',
                                            'Degree® Xtra herbicide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Capreno® herbicide',
                                            'Degree® Xtra herbicide',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Balance® Flexx herbicide',
                                        ['Harness® Xtra Herbicide','Harness® Xtra 5.6L Herbicide'],
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'DiFlexx® DUO herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'sorghum') {
                    $recommendation = [
                        'process' => [
                            '' => [
                                'Herbicides' => [
                                    'Degree® Xtra herbicide',
                                    'Warrant® Herbicide',
                                    'Huskie® herbicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                    'Sivanto™ Prime insecticide',
                                ],
                                'Seed Growth' => [
                                    'Gaucho® 600 insecticide',
                                    'Redigo® 480 fungicide',
                                    'CSI™ Safener 500 FS Sorghum seed protectant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            'This is a list of available products for Sorghum acres, not a recommendation',
                        ],
                    ];
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup PowerMAX® herbicide',
                                    'RT3® brand herbicide',
                                ],
                            ],
                            'Pre' => [
                                'Herbicides' => [
                                    'Olympus® Herbicide',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Huskie® Complete herbicide',
                                ],
                            ],
                            '' => [
                                'Fungicides' => [
                                    'Prosaro® fungicide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => false,
                    ];
                }
            } elseif ($region === 'Northern Plains') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'Warrant® Herbicide',
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Harness® MAX herbicide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Harness® MAX herbicide',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Oberon® 4 SC insecticide/miticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'TripleFLEX® II Herbicide',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'Harness® MAX herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'sorghum') {
                    $recommendation = [
                        'process' => [
                            '' => [
                                'Herbicides' => [
                                    'Degree® Xtra herbicide',
                                    'Warrant® Herbicide',
                                    'Huskie® herbicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                    'Sivanto™ Prime insecticide',
                                ],
                                'Seed Growth' => [
                                    'Gaucho® 600 insecticide',
                                    'Redigo® 480 fungicide',
                                    'CSI™ Safener 500 FS Sorghum seed protectant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            'This is a list of available products for Sorghum acres, not a recommendation',
                        ],
                    ];
                } elseif ($crop === 'wheat') {
                    if ($pass === 'winter wheat') {
                        $recommendation = [
                            'process' => [
                                'Burndown' => [
                                    'Herbicides' => [
                                        'RT3® brand herbicide',
                                    ],
                                ],
                                'Pre' => [
                                    'Herbicides' => [
                                        'Olympus® Herbicide',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Huskie® herbicide',
                                    ],
                                ],
                                '' => [
                                    'Fungicides' => [
                                        'Prosaro® fungicide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    } elseif ($pass === 'spring wheat') {
                        $recommendation = [
                            'process' => [
                                'Burndown' => [
                                    'Herbicides' => [
                                        'RT3® brand herbicide',
                                    ],
                                ],
                                'Pre' => [
                                    'Herbicides' => [
                                        'Olympus® Herbicide',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        ['Huskie® herbicide','Huskie® Complete herbicide','Luxxur® herbicide'],
                                    ],
                                ],
                                '' => [
                                    'Fungicides' => [
                                        'Prosaro® fungicide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                }
            } elseif ($region === 'Midsouth') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Pre' => [
                                'Herbicides' => [
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide***'],
                                    'Labeled DRA',
                                ],
                            ],
                            'Post 1' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Labeled DRA',
                                ],
                            ],
                            'Post 2' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide'],
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '***If you use Warrant® Ultra in a pre-emergence application, Warrant® must be used in the post-emergence application',
                            'In case of PPO resistant weeds, use Metribuzin based on soil type and pH in your pre-emergence application',
                            'In case of resistant grasses, also add clethodim in your postemergence application',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Balance® Flexx herbicide',
                                            ['Harness® Xtra Herbicide','Harness® Xtra 5.6L Herbicide'],
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Harness® MAX herbicide',
                                            'Atrazine',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Harness® MAX herbicide',
                                        'Atrazine',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'Capreno® herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'cotton') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    '2,4-D or dicamba',
                                ],
                            ],
                            'Pre' => [
                                'Herbicides' => [
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Fluometuron','Prometryn'],
                                    'Labeled DRA',
                                ],
                                'Insecticides/Nematicides' => [
                                    'Velum® Total insecticide/nematicide',
                                ],
                            ],
                            'Post 1' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                ],
                                'Plant Growth Regulator' => [
                                    'Stance® 110 SC plant regulator',
                                ],
                            ],
                            'Post 2*' => [
                                'Herbicides' => [
                                    'Liberty® Herbicide',
                                ],
                                'Fungicides' => [
                                    'Proline® fungicide',
                                ],
                                'Insecticides' => [
                                    'Sivanto™ Prime insecticide',
                                ],
                            ],
                            'Lay-By' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'Diuron or MSMA',
                                ],
                            ],
                            'Pre-Harvest' => [
                                'Defoliants' => [
                                    'Ginstar® EC cotton defoliant',
                                    'Finish® 6 PRO cotton defoliant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Bayer recommends two post over-the-top herbicide applications in Cotton',
                        ],
                    ];
                } elseif ($crop === 'sorghum') {
                    $recommendation = [
                        'process' => [
                            '' => [
                                'Herbicides' => [
                                    'Degree® Xtra herbicide',
                                    'Warrant® Herbicide',
                                    'Huskie® herbicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                    'Sivanto™ Prime insecticide',
                                ],
                                'Seed Growth' => [
                                    'Gaucho® 600 insecticide',
                                    'Redigo® 480 fungicide',
                                    'CSI™ Safener 500 FS Sorghum seed protectant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            'This is a list of available products for Sorghum acres, not a recommendation',
                        ],
                    ];
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup PowerMAX® herbicide',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Huskie® herbicide',
                                ],
                            ],
                            '' => [
                                'Fungicides' => [
                                    'Prosaro® fungicide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => false,
                    ];
                }
            } elseif ($region === 'Southern Plains') {
                if ($crop === 'soy') {
                    $recommendation = [
                        'process' => [
                            'Burndown/Pre' => [
                                'Herbicides' => [
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Metribuzin*',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    ['Warrant® Herbicide','Warrant® Ultra Herbicide**'],
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Delaro® 325 SC fungicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Use Metribuzin rate based on soil type and pH',
                            '**East of Hwy 77 in TX and East of Hwy 75 in OK',
                        ],
                    ];
                } elseif ($crop === 'corn') {
                    if ($pass == 1) {
                        if ($section === 'Pre') {
                            $recommendation = [
                                'process' => [
                                    'Pre' => [
                                        'Herbicides' => [
                                            'Balance® Flexx herbicide',
                                            'Harness® MAX herbicide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        } elseif ($section === 'Post') {
                            $recommendation = [
                                'process' => [
                                    'Post' => [
                                        'Herbicides' => [
                                            'Roundup® brand agricultural herbicide',
                                            'Harness® MAX herbicide',
                                        ],
                                        'Fungicides' => [
                                            'Delaro® 325 SC fungicide',
                                        ],
                                        'Insecticides' => [
                                            'Baythroid® XL insecticide',
                                        ],
                                    ],
                                ],
                                'incentive' => false,
                                'footnotes' => false,
                            ];
                        }
                    } elseif ($pass == 2) {
                        $recommendation = [
                            'process' => [
                                'Pre' => [
                                    'Herbicides' => [
                                        'Harness® MAX herbicide',
                                    ],
                                ],
                                'Post' => [
                                    'Herbicides' => [
                                        'Roundup® brand agricultural herbicide',
                                        'DiFlexx® DUO herbicide',
                                    ],
                                    'Fungicides' => [
                                        'Delaro® 325 SC fungicide',
                                    ],
                                    'Insecticides' => [
                                        'Baythroid® XL insecticide',
                                    ],
                                ],
                            ],
                            'incentive' => false,
                            'footnotes' => false,
                        ];
                    }
                } elseif ($crop === 'cotton') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    '2,4-D or dicamba',
                                ],
                            ],
                            'Pre' => [
                                'Herbicides' => [
                                    ['Warrant® Herbicide','Fluometuron','Prometryn'],
                                ],
                                'Insecticides/Nematicides' => [
                                    'Velum® Total insecticide/nematicide',
                                ],
                            ],
                            'Post 1' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Warrant® Herbicide',
                                    'Labeled DRA',
                                ],
                                'Plant Growth Regulator' => [
                                    'Stance® 110 SC plant regulator',
                                ],
                            ],
                            'Post 2*' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'XtendiMax® herbicide with VaporGrip® Technology',
                                    'Labeled DRA',
                                ],
                                'Fungicides' => [
                                    'Proline® fungicide',
                                ],
                                'Insecticides' => [
                                    'Sivanto™ Prime insecticide',
                                ],
                            ],
                            'Lay-By' => [
                                'Herbicides' => [
                                    'Roundup® brand agricultural herbicide',
                                    'Diuron or MSMA',
                                ],
                            ],
                            'Pre-Harvest' => [
                                'Defoliants' => [
                                    'Ginstar® EC cotton defoliant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            '*Bayer recommends two post over-the-top herbicide applications in Cotton',
                        ],
                    ];
                } elseif ($crop === 'sorghum') {
                    $recommendation = [
                        'process' => [
                            '' => [
                                'Herbicides' => [
                                    'Degree® Xtra herbicide',
                                    'Warrant® Herbicide',
                                    'Huskie® herbicide',
                                ],
                                'Insecticides' => [
                                    'Baythroid® XL insecticide',
                                    'Sivanto™ Prime insecticide',
                                ],
                                'Seed Growth' => [
                                    'Gaucho® 600 insecticide',
                                    'Redigo® 480 fungicide',
                                    'CSI™ Safener 500 FS Sorghum seed protectant',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => [
                            'This is a list of available products for Sorghum acres, not a recommendation',
                        ],
                    ];
                } elseif ($crop === 'wheat') {
                    $recommendation = [
                        'process' => [
                            'Burndown' => [
                                'Herbicides' => [
                                    'Roundup PowerMAX® herbicide',
                                    'RT3® brand herbicide',
                                ],
                            ],
                            'Pre' => [
                                'Herbicides' => [
                                    'Olympus® Herbicide',
                                ],
                            ],
                            'Post' => [
                                'Herbicides' => [
                                    'Huskie® herbicide',
                                ],
                            ],
                            '' => [
                                'Fungicides' => [
                                    'Prosaro® fungicide',
                                ],
                            ],
                        ],
                        'incentive' => false,
                        'footnotes' => false,
                    ];
                }
            }
        }


        // assemble product descriptions manually
        // name,description,group numbers (CSV),image (if exists in img/products)
        $products = [
            'Balance® Flexx herbicide' => [
                'desc' => 'cost-effective, flexible foundation (a restricted use pesticide)',
                'groups' => ['27'],
                'image' => 'balance-flexx.png',
            ],
            'Baythroid® XL insecticide' => [
                'desc' => 'complete pyrethroid for fast, effective control of labeled primary and secondary pests',
                'groups' => ['3A'],
                'image' => 'baythroid-xl.png',
            ],
            'Capreno® herbicide' => [
                'desc' => 'longest-lasting residual of any postemergence herbicide',
                'groups' => ['2','27'],
                'image' => 'capreno.png',
            ],
            'Corvus® herbicide' => [
                'desc' => 'reactivation delivers early-season win against weeds (a restricted use pesticide)',
                'groups' => ['2','27'],
                'image' => 'corvus.png',
            ],
            'CSI™ Safener 500 FS Sorghum seed protectant' => [
                'desc' => 'protects against herbicidal injury',
                'groups' => ['N/A'],
                'image' => 'csi-safener-500-fs.png',
            ],
            'Degree® Xtra herbicide' => [
                'desc' => 'patented temperature-release technology delivers just the right amount of control',
                'groups' => ['5','15'],
                'image' => 'degree-xtra.png',
            ],
            'Delaro® 325 SC fungicide' => [
                'desc' => 'broader spectrum of disease control and best-in-class, dual mode of action residual',
                'groups' => ['3','11'],
                'image' => 'delaro-325-sc.png',
            ],
            'DiFlexx® DUO herbicide' => [
                'desc' => 'powerful control against the full range of tough weeds',
                'groups' => ['4','27'],
                'image' => 'diflexx-duo.png',
            ],
            'EverGol™ Energy & Gaucho 600 Blend' => [
                'desc' => 'highly effective against seed and soilborne diseases like stand-damaging Rhizoctonia root rot and profit-robbing smuts in cereals',
                'groups' => ['3','4','7'],
                'image' => 'evergol.png',
            ],
            'Finish® 6 PRO cotton defoliant' => [
                'desc' => 'The only hormonal defoliant and boll opener premix',
                'groups' => ['N/A'],
                'image' => 'finish6-pro.png',
            ],
            'Gaucho® 600 insecticide' => [
                'desc' => 'controls insects that spread viruses',
                'groups' => ['N/A'],
                'image' => 'gaucho-600.png',
            ],
            'Ginstar® EC cotton defoliant' => [
                'desc' => 'superior regrowth control regardless of the conditions',
                'groups' => ['N/A'],
                'image' => 'ginstar-ec.png',
            ],
            'Harness® MAX herbicide' => [
                'desc' => 'residual activity with added benefit of mesotrione for postemergence activity',
                'groups' => ['15','27'],
                'image' => 'harness-max.png',
            ],
            'Harness® Xtra Herbicide' => [
                'desc' => 'premix with Atrazine for expanded control',
                'groups' => ['5','15'],
                'image' => 'harness-xtra.png',
            ],
            'Harness® Xtra 5.6L Herbicide' => [
                'desc' => 'premix with Atrazine for expanded control',
                'groups' => ['5','15'],
                'image' => 'harness-xtra.png',
            ],
            'Huskie® herbicide' => [
                'desc' => 'effectively controls a wide spectrum of broadleaf weeds, including marestail, kochia and Russian thistle',
                'groups' => ['6','27'],
                'image' => 'huskie.png',
            ],
            'Huskie® Complete herbicide' => [
                'desc' => 'the all-in-one solution to weed control, delivering complete, cross-spectrum control of both grass and broadleaf weeds',
                'groups' => ['2','6','27'],
                'image' => 'huskie-complete.png',
            ],
            'Luxxur® herbicide' => [
                'desc' => 'one-of-a-kind combination that not only knocks out wild oats but also handles other hard-to-control weeds',
                'groups' => ['2'],
                'image' => 'luxxur.png',
            ],
            'Oberon® 4 SC insecticide/miticide' => [
                'desc' => 'Long-lasting residual activity on all mite life stages',
                'groups' => ['23'],
                'image' => 'oberon-4-sc.png',
            ],
            'Olympus® Herbicide' => [
                'desc' => 'cost-efficient, effective grass control with tank-mix flexibility',
                'groups' => ['2'],
                'image' => 'olympus.png',
            ],
            'Osprey® herbicide' => [
                'desc' => 'keep your crop choices open with rotational flexibility and gain control over tough grassy weeds in your winter wheat.',
                'groups' => ['2'],
                'image' => 'osprey.png',
            ],
            'Proline® fungicide' => [
                'desc' => 'go-to solution to help manage disease all season long and maximize yield potential',
                'groups' => ['3'],
                'image' => 'proline.png',
            ],
            'Prosaro® fungicide' => [
                'desc' => 'improves grain quality and maximizes yield through its unbeatable spectrum of disease control, increasing your profit potential',
                'groups' => ['3'],
                'image' => 'prosaro.png',
            ],
            'Raxil® PRO Shield seed treatment' => [
                'desc' => 'all-in-one fungicide and insecticide formulation for broad-spectrum disease control plus insect control, including wireworms and aphids',
                'groups' => ['3','4'],
                'image' => 'raxil.png',
            ],
            'Redigo® 480 fungicide' => [
                'desc' => 'protects against seedborne fungi from planting to emergence',
                'groups' => ['3'],
                'image' => 'redigo-480.png',
            ],
            'RT3® brand herbicide' => [
                'desc' => 'reliable, consistent performance on tough-to-control weeds with preplant, post-harvest and fallow applications',
                'groups' => ['9'],
                'image' => 'rt3.png',
            ],
            'Roundup® brand agricultural herbicide' => [
                'desc' => 'consistent, reliable control of tough-to-control weeds',
                'groups' => ['9'],
                'image' => 'roundup.png',
            ],
            'Roundup PowerMAX® herbicide' => [
                'desc' => 'offers enhanced consistency of control, weed to weed and field to field',
                'groups' => ['9'],
                'image' => 'roundup_powermax.png',
            ],
            'Sivanto™ Prime insecticide' => [
                'desc' => 'maintains beneficial insect populations, including predatory mites',
                'groups' => ['4D'],
                'image' => 'sivanto.png',
            ],
            'Stance® 110 SC plant regulator' => [
                'desc' => 'affects two key plant hormones for consistent vegetative management',
                'groups' => ['N/A'],
                'image' => 'stance-110-sc.png',
            ],
            'TripleFLEX® II Herbicide' => [
                'desc' => 'control of ALS-, glyphosate- or triazine-resistant weeds',
                'groups' => ['2','4','15'],
                'image' => 'tripleflex-ii.png',
            ],
            'Velum® Total insecticide/nematicide' => [
                'desc' => 'in-furrow solution for long-lasting control of nematodes and early-season insects',
                'groups' => ['4A','7'],
                'image' => 'velum-total.png',
            ],
            'Warrant® Herbicide' => [
                'desc' => 'pre- and postemergence residual herbicide with microencapsulation technology',
                'groups' => ['15'],
                'image' => 'warrant.png',
            ],
            'Warrant® Ultra Herbicide' => [
                'desc' => 'go-to residual benefits of Warrant® Herbicide with the added postemergence and residual activity of fomesafen',
                'groups' => ['14','15'],
                'image' => 'warrant-ultra.png',
            ],
            'Warrant® Ultra Herbicide**' => [
                'desc' => 'go-to residual benefits of Warrant® Herbicide with the added postemergence and residual activity of fomesafen',
                'groups' => ['14','15'],
                'image' => 'warrant-ultra.png',
            ],
            'Warrant® Ultra Herbicide***' => [
                'desc' => 'go-to residual benefits of Warrant® Herbicide with the added postemergence and residual activity of fomesafen',
                'groups' => ['14','15'],
                'image' => 'warrant-ultra.png',
            ],
            'XtendiMax® herbicide with VaporGrip® Technology' => [
                'desc' => 'helps manage glyphosate-resistant broadleaf weeds (a restricted use pesticide)',
                'groups' => ['4'],
                'image' => 'xtendimax-vgt.png',
            ],
            'Atrazine' => [
                'desc' => 'N/A',
                'groups' => ['N/A'],
            ],
            'Labeled DRA' => [
                'desc' => 'Drift Reducing Adjuvant',
                'groups' => ['N/A'],
            ],
            'Paraquat' => [
                'desc' => 'N/A',
                'groups' => ['1'],
            ],
            'Diuron' => [
                'desc' => 'N/A',
                'groups' => ['7'],
            ],
            'Metribuzin*' => [
                'desc' => 'N/A',
                'groups' => ['N/A'],
            ],
            'MSMA' => [
                'desc' => 'N/A',
                'groups' => ['17'],
            ],
            'Fluometuron' => [
                'desc' => 'N/A',
                'groups' => ['N/A'],
            ],
            'Prometryn' => [
                'desc' => 'N/A',
                'groups' => ['N/A'],
            ],
        ];

        if ($recommendation['process']) {
            foreach($recommendation['process'] as $process => $type) {
                foreach($type as $type_name => $types) {
                    foreach($types as $key => $n) {
                        if (!is_array($n)) {
                            $product_name = $n;
                            if (isset($products[$product_name])) {
                                $products[$product_name]['name'] = $product_name;
                                $recommendation['process'][$process][$type_name][$key] = $products[$product_name];
                            } else {
                                $recommendation['process'][$process][$type_name][$key] = ['name' => $product_name,'desc' => 'N/A','groups' => ['N/A']];
                            }
                        } else {
                            foreach($n as $i => $num) {
                                $product_name = $num;
                                $first = $i == 0;
                                $last = $i == count($n)-1;
                                if ($first) {
                                    unset($recommendation['process'][$process][$type_name][$key]);
                                }
                                if (isset($products[$product_name])) {
                                    $products[$product_name]['name'] = $product_name;
                                    $final_piece = $products[$product_name];
                                } else {
                                    $final_piece = ['name' => $product_name,'desc' => 'N/A','groups' => ['N/A']];
                                }
                                $final_piece['first_alt'] = $first;
                                $final_piece['last_alt'] = $last;
                                $recommendation['process'][$process][$type_name][] = $final_piece;
                            }
                        }
                        
                    }
                }
            }
        }

        return $recommendation;
    }

    public function result(Request $request)
    {
        $region = $request->input('region');
        $crop = $request->input('crop');
        $pass = $request->input('pass');
        $section = $request->input('section');

        $recommendation = $this->getRecommendation($region, $crop, $pass, $section);

        return view('result', [
            'region' => $region,
            'crop' => $crop,
            'pass' => $pass,
            'section' => $section,
            'recommendation' => $recommendation,
        ]);
    }

    // public function sendResultEmail(Request $request)
    // {
    //     $region = $request->input('region');
    //     $crop = $request->input('crop');
    //     $pass = $request->input('pass');
    //     $section = $request->input('section');
    //     $email = $request->input('email');

    //     if (empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    //         return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => $section]));
    //     }

    //     Mail::send('emails.send_results', ['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => $section], function($message) use ($email, $region, $crop) {
    //         $message->to($email);
    //         $message->subject("Your " . $region . " " . $crop . " recommendation is here | Commodity Classic 2019");
    //     });

    //     return Redirect::to('/result?' . http_build_query(['region' => $region, 'crop' => $crop, 'pass' => $pass, 'section' => $section]));
    // }

    // // largely for testing
    // public function viewResultEmail(Request $request)
    // {
    //     $region = $request->input('region');
    //     $crop = $request->input('crop');
    //     $pass = $request->input('pass');
    //     $section = $request->input('section');

    //     return view('emails.send_results', [
    //         'region' => $region,
    //         'crop' => $crop,
    //         'pass' => $pass,
    //         'section' => $section,
    //     ]);
    // }
}
