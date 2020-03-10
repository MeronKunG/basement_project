<?php

namespace App\Controller;

use App\Form\DateFilterType;
use App\Repository\TestAllParcelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request, TestAllParcelRepository $testAllParcelRepository)
    {
        date_default_timezone_set("Asia/Bangkok");
        $session = new Session();
        $form = $this->createForm(DateFilterType::class);
        $form->handleRequest($request);

        if (empty($session->get('dateMonth'))) {
            $date = date("Ym", strtotime("-1 month"));
        } else {
            $date = $session->get('dateMonth');
        }

        if ($form->isSubmitted()) {
            $formData = $request->request->get('date_filter');
            $session->set('dateMonth', $formData['getYear'] . $formData['getMonth']);
            return $this->redirect($this->generateUrl('dashboard'));
        }
        $parcelObj = [];
        // get Parcel Data //////////////////////////////////////////////////////////////////////////////////
        $parcelDataObj = $testAllParcelRepository->getCountDataByDate($date, 'N');
        $parcelDate = $testAllParcelRepository->getDateByDate($date);
        foreach ($parcelDataObj as $key => $val) {
            $parcelObj[$val['shopname']][] = $val;
        }

        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        // Top 5 Data ///////////////////////////////////////////////////////////////////////////////////////
        foreach ($parcelObj as $key => $val) {
            $parcelObj[$key] = [
                'name' => $parcelObj[$key][0]['shopname'],
                'amounts' => count($parcelObj[$key]),
                'per' => round(count($parcelObj[$key]) / count($parcelDate), 2),
                'percent' => round((count($parcelObj[$key]) / count($parcelDataObj)) * 100, 2)
            ];
        }

        $parcelObj2 = $parcelObj;

        usort($parcelObj, function ($item1, $item2) {
            return $item2['amounts'] <=> $item1['amounts'];
        });

        /////////////////////////////////////////////////////////////////////////////////////////////////////
        $returnRawData = [];
        $dataResult = [];
        $returnData = [];
        $returnDataTran = [];

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        $parcelObjSlice = array_slice($parcelObj, 0, 5);
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($parcelObjSlice as $key => $val) {
            $returnRawData[] = $testAllParcelRepository->getDateNameByDateAndShopName($date, 'N', $parcelObjSlice[$key]['name']);
        }

        foreach ($parcelObjSlice as $key => $val) {
            $t1 = ($t1 + $parcelObjSlice[$key]['amounts']);
            $t2 = ($t2 + $parcelObjSlice[$key]['per']);
            $t3 = ($t3 + $parcelObjSlice[$key]['percent']);
        }

        // Return Data //////////////////////////////////////////////////////////////////////////////
        foreach ($returnRawData as $key => $val) {
            foreach ($val as $k => $v) {
                $returnData[$v['shopname']][$v['statuscode']][] = $val;
            }
        }

        foreach ($returnData as $key => $val) {
            $test = [
                104 => 0,
                105 => 0,
                106 => 0
            ];
            foreach ($val as $k => $v) {
                if (array_key_exists($k, $test)) {
                    $test[$k] = count($v);
                } else {
                    $test[$k] = 0;
                }
            }
            $dataResult[$key] = [
                'path' => $test,
            ];
        }

        $dataResult = array_values($dataResult);

        // Total /////////////////////////////////////////////////////////////////////////////////
        $total_price = [];
        for ($i = 0; $i < count($dataResult); $i++) {
            $total_price[] = round(($dataResult[$i]['path'][106] / $parcelObjSlice[$i]['amounts'] * 100), 2);
        }

        $returnDataSlice = array_slice($dataResult, 0, 5);

        $total = [
            't_amount' => $t1,
            't_per' => $t2,
            't_percent' => $t3,
            't_return' => $total_price
        ];

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Top 10 Data ////////////////////////////////////////////////////////////////////////////

        $dataTop10 = [];
        $total_price2 = [];
        $transportTypeRawObj = $testAllParcelRepository->getDateNameByDateAndTransportType($date, 'N');
        foreach ($transportTypeRawObj as $key => $val) {
            $returnDataTran[$val['shopname']][$val['transportType']][] = $val;
        }

        foreach ($returnDataTran as $key => $val) {
            $transportType = [
                'cod' => 0,
                'normal' => 0
            ];
            foreach ($val as $k => $v) {
                if (array_key_exists($k, $transportType)) {
                    $transportType[$k] = count($v);
                } else {
                    $transportType[$k] = 0;
                }
            }
            $dataTop10[$key] = [
                'name' => $key,
                'type' => $transportType,
            ];
        }
        foreach ($dataTop10 as $key => $val) {
            $total_price2[$key] = round((($dataTop10[$key]['type']['cod'] + $dataTop10[$key]['type']['normal']) / $parcelObj2[$key]['amounts'] * 100),
                2);
            $dataTop10[$key][] = [
                'amount' => $parcelObj2[$key]['amounts'],
                'total' => $dataTop10[$key]['type']['cod'] + $dataTop10[$key]['type']['normal'],
                'percent' => $total_price2[$key]
            ];
        }

//        usort($dataTop10, function ($item1, $item2) {
//            return ($item2['type']['cod'] + $item2['type']['normal']) <=> ($item1['type']['cod'] + $item1['type']['normal']);
//        });

        usort($dataTop10, function ($item1, $item2) {
            return $item2[0]['percent'] <=> $item1[0]['percent'];
        });


        $returnData2Slice = array_slice($dataTop10, 0, 10);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $lastDataObj = [];
        $lastDataRawObj = $testAllParcelRepository->getLastDataByDate($date, 'N');
        foreach ($lastDataRawObj as $key => $val) {
            $lastDataObj[$val['haul']][$val['statuscode']][] = $val;
        }
        $last_total = 0;
        foreach ($lastDataObj as $key => $val) {
            $lastDataObj[$key]['total'] = 0;
            foreach ($val as $k => $v) {
                $last_total = $last_total + count($lastDataObj[$key][$k]);
                $lastDataObj[$key][$k] = count($lastDataObj[$key][$k]);
                $lastDataObj[$key]['total'] = $lastDataObj[$key]['total']+($lastDataObj[$key][$k]);
            }
        }

        foreach ($lastDataObj as $key => $val) {
            $lastDataObj[$key][105] = ['amount' => $lastDataObj[$key][105], 'percent' => round(($lastDataObj[$key][105] / $lastDataObj[$key]['total']) *100, 2)];
        }

        $lastDataObj['total'] = $last_total;
        return $this->render('dashboard/index.html.twig', [
            'form' => $form->createView(),
            'parcelData' => $parcelObjSlice,
            'test2' => $returnDataSlice,
            'total' => $total,
            'parcelData2' => $returnData2Slice,
            'lastData' => $lastDataObj
        ]);
    }

    /**
     * @Route("/dashboard_hybrid", name="dashboard_hybrid")
     */
    public function dashboard_hybrid(Request $request, TestAllParcelRepository $testAllParcelRepository)
    {
        date_default_timezone_set("Asia/Bangkok");
        $session = new Session();
        $form = $this->createForm(DateFilterType::class);
        $form->handleRequest($request);

        if (empty($session->get('dateMonth'))) {
            $date = date("Ym", strtotime("-1 month"));
        } else {
            $date = $session->get('dateMonth');
        }

        if ($form->isSubmitted()) {
            $formData = $request->request->get('date_filter');
            $session->set('dateMonth', date("Y") . $formData['getMonth']);
            return $this->redirect($this->generateUrl('dashboard'));
        }
        $parcelObj = [];
        // get Parcel Data //////////////////////////////////////////////////////////////////////////////////
        $parcelDataObj = $testAllParcelRepository->getCountDataByDate($date, 'Y');
        $parcelDate = $testAllParcelRepository->getDateByDate($date);
        foreach ($parcelDataObj as $key => $val) {
            $parcelObj[$val['ffmOwner']][] = $val;
        }

        $t1 = 0;
        $t2 = 0;
        $t3 = 0;
        // Top 5 Data ///////////////////////////////////////////////////////////////////////////////////////
        foreach ($parcelObj as $key => $val) {
            $parcelObj[$key] = [
                'name' => $parcelObj[$key][0]['ffmOwner'],
                'amounts' => count($parcelObj[$key]),
                'per' => round(count($parcelObj[$key]) / count($parcelDate), 2),
                'percent' => round((count($parcelObj[$key]) / count($parcelDataObj)) * 100, 2)
            ];
        }

        $parcelObj2 = $parcelObj;

        usort($parcelObj, function ($item1, $item2) {
            return $item2['amounts'] <=> $item1['amounts'];
        });

        /////////////////////////////////////////////////////////////////////////////////////////////////////
        $returnRawData = [];
        $dataResult = [];
        $returnData = [];
        $returnDataTran = [];

        ///////////////////////////////////////////////////////////////////////////////////////////////////
        $parcelObjSlice = array_slice($parcelObj, 0, 5);
        ///////////////////////////////////////////////////////////////////////////////////////////////////

        foreach ($parcelObjSlice as $key => $val) {
            $returnRawData[] = $testAllParcelRepository->getDateNameByDateAndOwnerName($date, 'Y', $parcelObjSlice[$key]['name']);
        }

        foreach ($parcelObjSlice as $key => $val) {
            $t1 = ($t1 + $parcelObjSlice[$key]['amounts']);
            $t2 = ($t2 + $parcelObjSlice[$key]['per']);
            $t3 = ($t3 + $parcelObjSlice[$key]['percent']);
        }

        // Return Data //////////////////////////////////////////////////////////////////////////////
        foreach ($returnRawData as $key => $val) {
            foreach ($val as $k => $v) {
                $returnData[$v['ffmOwner']][$v['statuscode']][] = $val;
            }
        }

        foreach ($returnData as $key => $val) {
            $test = [
                104 => 0,
                105 => 0,
                106 => 0
            ];
            foreach ($val as $k => $v) {
                if (array_key_exists($k, $test)) {
                    $test[$k] = count($v);
                } else {
                    $test[$k] = 0;
                }
            }
            $dataResult[$key] = [
                'path' => $test,
            ];
        }

        $dataResult = array_values($dataResult);

        // Total /////////////////////////////////////////////////////////////////////////////////
        $total_price = [];
        for ($i = 0; $i < count($dataResult); $i++) {
            $total_price[] = round(($dataResult[$i]['path'][106] / $parcelObjSlice[$i]['amounts'] * 100), 2);
        }

        $returnDataSlice = array_slice($dataResult, 0, 5);

        $total = [
            't_amount' => $t1,
            't_per' => $t2,
            't_percent' => $t3,
            't_return' => $total_price
        ];

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Top 10 Data ////////////////////////////////////////////////////////////////////////////

        $dataTop10 = [];
        $total_price2 = [];
        $transportTypeRawObj = $testAllParcelRepository->getDateNameByDateAndTransportType($date, 'Y');
        foreach ($transportTypeRawObj as $key => $val) {
            $returnDataTran[$val['ffmOwner']][$val['transportType']][] = $val;
        }

        foreach ($returnDataTran as $key => $val) {
            $transportType = [
                'cod' => 0,
                'normal' => 0
            ];
            foreach ($val as $k => $v) {
                if (array_key_exists($k, $transportType)) {
                    $transportType[$k] = count($v);
                } else {
                    $transportType[$k] = 0;
                }
            }
            $dataTop10[$key] = [
                'name' => $key,
                'type' => $transportType,
            ];
        }
        foreach ($dataTop10 as $key => $val) {
            $total_price2[$key] = round((($dataTop10[$key]['type']['cod'] + $dataTop10[$key]['type']['normal']) / $parcelObj2[$key]['amounts'] * 100),
                2);
            $dataTop10[$key][] = [
                'amount' => $parcelObj2[$key]['amounts'],
                'total' => $dataTop10[$key]['type']['cod'] + $dataTop10[$key]['type']['normal'],
                'percent' => $total_price2[$key]
            ];
        }

        usort($dataTop10, function ($item1, $item2) {
            return $item2[0]['percent'] <=> $item1[0]['percent'];
        });


        $returnData2Slice = array_slice($dataTop10, 0, 10);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $lastDataObj = [];
        $lastDataRawObj = $testAllParcelRepository->getLastDataByDate($date, 'Y');
        foreach ($lastDataRawObj as $key => $val) {
            $lastDataObj[$val['haul']][$val['statuscode']][] = $val;
        }
        $last_total = 0;
        foreach ($lastDataObj as $key => $val) {
            $lastDataObj[$key]['total'] = 0;
            foreach ($val as $k => $v) {
                $last_total = $last_total + count($lastDataObj[$key][$k]);
                $lastDataObj[$key][$k] = count($lastDataObj[$key][$k]);
                $lastDataObj[$key]['total'] = $lastDataObj[$key]['total']+($lastDataObj[$key][$k]);
            }
        }

        foreach ($lastDataObj as $key => $val) {
            $lastDataObj[$key][105] = ['amount' => $lastDataObj[$key][105], 'percent' => round(($lastDataObj[$key][105] / $lastDataObj[$key]['total']) *100, 2)];
        }

        $lastDataObj['total'] = $last_total;

        return $this->render('dashboard/dashboard_hybrid.html.twig', [
            'form' => $form->createView(),
            'parcelData' => $parcelObjSlice,
            'test2' => $returnDataSlice,
            'total' => $total,
            'parcelData2' => $returnData2Slice,
            'lastData' => $lastDataObj
        ]);
    }
}
