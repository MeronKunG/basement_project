<?php

namespace App\Controller;

use App\Entity\CsLogMailcode;
use App\Entity\TestAllParcel;
use App\Entity\TestCsReturnLog;
use App\Form\DateReturnType;
use App\Form\MerchantBillingDeliveryType;
use App\Form\RemarksType;
use App\Form\ReturnReasonType;
use App\Form\SearchPhoneType;
use App\Repository\GlobalAuthenRepository;
use App\Repository\GlobalOrderStatusRepository;
use App\Repository\GlobalTransporterRepository;
use App\Repository\MerchantBillingDeliveryRepository;
use App\Repository\MerchantBillingDetailRepository;
use App\Repository\MerchantBillingRepository;
use App\Repository\MerchantConfigRepository;
use App\Repository\ParcelMemberRepository;
use App\Repository\ParcelTempCaptureRepository;
use App\Repository\ParcelTempDataRepository;
use App\Repository\PcComApprovedRepository;
use App\Repository\PostInfoZipcodesRepository;
use App\Repository\TestAllParcelRepository;
use App\Repository\TestCsReturnCollectRepository;
use App\Repository\TestCSRemarksStatusRepository;
use App\Repository\TestCsReturnReasonSubRepository;
use App\Repository\TestEcomToPreakLogRepository;
use App\Repository\CodReturnRepository;
use App\Repository\TrackingLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Faker\Factory;

class ParcelController extends AbstractController
{
    /**
     * @Route("/queue", name="parcel")
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        ParcelTempDataRepository $parcelTempDataRepository,
        PcComApprovedRepository $pcComApprovedRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $faker = Factory::create();
        $session = new Session();
        $result = array();
        $resultGenerateData = array();
        $resultData = array();
        $merchantData = array();
        $form2 = $this->createForm(RemarksType::class);
        $form2->handleRequest($request);
        if ($session->get('type') != null) {
            if ($session->get('check') == 'yes') {
                if ($session->get('billing') == 'codnormal') {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'), $session->get('shopId'));
                } else {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'), $session->get('shopId'));
                }
                $numResult = count($dataFormAdjustment);
                foreach ($dataFormAdjustment as $element) {
                    $result[$element['priority']][] = $element;
                }
                for ($i = 0; $i < array_key_first($result); $i++) {
                    $resultGenerateData = $result[array_key_first($result)];
                }
                $randomMailCode = $randomMailCode = $faker->randomElement($resultGenerateData);
            } else {
                if ($session->get('billing') == 'codnormal') {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'), $session->get('shopId'));
                } else {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'), $session->get('shopId'));
                }
                $numResult = count($dataFormAdjustment);
                foreach ($dataFormAdjustment as $element) {
                    $result[$element['priority']][] = $element;
                }
                for ($i = 0; $i < array_key_first($result); $i++) {
                    $resultGenerateData = $result[array_key_first($result)];
                }
                $randomMailCode = $randomMailCode = $faker->randomElement($resultGenerateData);
            }
            $resultMerchantName = $merchantConfigRepository->getMerchantNameByByTakeOrderByArray($session->get('shopId'));
            foreach ($resultMerchantName as $t1) {
                $merchantData[$t1['merchantname']] = $t1['takeorderby'];
            }
            // merchant_billing_delivery
            $merchantBillingDeliveryObj = $merchantBillingDeliveryRepository->getInvoiceAndTakeOrderByByEmailCode($randomMailCode['mailcode']);
            if ($merchantBillingDeliveryObj != null) {
                $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
                // merchant_billing
                $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($merchantBillingObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'orderphoneno') {
                            $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // merchant_billing_detail
                $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                    // Global_Transporter
                    $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
                } else {
                    $transporterObj = null;
                }
                // global_orderstatus
                $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
                // global_authen
                $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
                // merchant_config
                $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
                // parcel_temp_capture
                $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($randomMailCode['mailcode']);
                // tracking_log
                $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($randomMailCode['mailcode']);
                // parcel_Temp_Data
                $parcelTempDataObj = $parcelTempDataRepository->getDataByConsignmentNo($randomMailCode['mailcode']);
                foreach ($parcelTempDataObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'operator') {
                            $parcelTempDataObj[$key]['operator'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // pc_com_approved
                $pcComAppObj = $pcComApprovedRepository->getTransferAmtByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                // parcel_member
                $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
                foreach ($parcelMemberObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'phoneregis') {
                            $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // postinfo_zipcode
                $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
                $resultData = array(
                    'paymentInvoice' => $merchantBillingDeliveryObj,
                    'merchantBilling' => $merchantBillingObj,
                    'merchantDetail' => $merchantDetailObj,
                    'COD' => $merchantBillingDeliveryCODResult,
                    'transporter' => $transporterObj,
                    'globalOrderStatus' => $globalOrderStatusObj,
                    'parcelTemp' => $parcelTempObj,
                    'trackingLog' => $trackingLogObj,
                    'globalAuthen' => $globalAuthenObj->getFname(),
                    'merchantConfig' => $merchantConfigObj,
                    'parcelMember' => $parcelMemberObj,
                    'zipCode' => $zipCodeObj,
                    'parcelTempData' => $parcelTempDataObj,
                    'pcComApp' => $pcComAppObj
                );
            }
        } else {
            return $this->redirect($this->generateUrl('adjustment'));
        }

        return $this->render('parcel/index.html.twig', [
            'form2' => $form2->createView(),
            'randomMailCode' => $randomMailCode,
            'numResult' => $numResult,
            'resultData' => $resultData,
            'merchantName' => array_keys($merchantData)
        ]);
    }

    /**
     * @Route("/queuesearch", name="queuesearch")
     */
    public function queueInput(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        ParcelTempDataRepository $parcelTempDataRepository,
        PcComApprovedRepository $pcComApprovedRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository,
        CodReturnRepository $codReturnRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $resultData = array();
        $form = $this->createForm(MerchantBillingDeliveryType::class);
        $form->handleRequest($request);
        $form2 = $this->createForm(RemarksType::class);
        $form2->handleRequest($request);
        $form3 = $this->createForm(ReturnReasonType::class);
        $form3->handleRequest($request);
        if ($form->get('mailcode')->getData() && $form->isSubmitted()) {
            // merchant_billing_delivery
            if (strlen($form->get('mailcode')->getData()) <= 12 || $this->endsWith($form->get('mailcode')->getData(), "TH")) {
                $merchantBillingDeliveryObj = $merchantBillingDeliveryRepository->getInvoiceAndTakeOrderByByEmailCode($form->get('mailcode')->getData());
            } else {
                $merchantBillingDeliveryObj = $merchantBillingDeliveryRepository->getInvoiceAndTakeOrderByByPaymentInvoice($form->get('mailcode')->getData());
            }
            if ($merchantBillingDeliveryObj != null) {
                $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
                // merchant_billing
                $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($merchantBillingObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'orderphoneno') {
                            $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // merchant_billing_detail
                $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                    // Global_Transporter
                    $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
                } else {
                    $transporterObj = null;
                }
                // global_orderstatus
                $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
                // global_authen
                $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
                // merchant_config
                $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
                // parcel_temp_capture
                $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // tracking_log
                $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // parcel_member
                $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
                // parcel_Temp_Data
                $parcelTempDataObj = $parcelTempDataRepository->getDataByConsignmentNo($merchantBillingDeliveryObj[0]['mailcode']);
                foreach ($parcelTempDataObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'operator') {
                            $parcelTempDataObj[$key]['operator'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // pc_com_approved
                $pcComAppObj = $pcComApprovedRepository->getTransferAmtByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($parcelMemberObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'phoneregis') {
                            $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // postinfo_zipcode
                $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
                // cod_return
                $codReturnObj = $codReturnRepository->getScanDataAndImgUrlByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                $resultData = array(
                    'paymentInvoice' => $merchantBillingDeliveryObj,
                    'merchantBilling' => $merchantBillingObj,
                    'merchantDetail' => $merchantDetailObj,
                    'COD' => $merchantBillingDeliveryCODResult,
                    'transporter' => $transporterObj,
                    'globalOrderStatus' => $globalOrderStatusObj,
                    'parcelTemp' => $parcelTempObj,
                    'trackingLog' => $trackingLogObj,
                    'globalAuthen' => $globalAuthenObj->getFname(),
                    'merchantConfig' => $merchantConfigObj,
                    'parcelMember' => $parcelMemberObj,
                    'zipCode' => $zipCodeObj,
                    'parcelTempData' => $parcelTempDataObj,
                    'pcComApp' => $pcComAppObj,
                    'codReturn' => $codReturnObj
                );
            } else {
                $this->addFlash('error', 'ไม่พบข้อมูล / ข้อมูลไม่ถูกต้อง !');
                return $this->redirect($this->generateUrl('queuesearch'));
            }
        }

        return $this->render('parcel/queueinput.html.twig', [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'form3' => $form3->createView(),
            'resultData' => $resultData,
        ]);
    }

    /**
     * @Route("/csreturn", name="csreturn")
     */
    public function csReturn(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository,
        TestCsReturnCollectRepository $testCsReturnCollectRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $session = new Session();
        $resultData = array();
        $statusObj = $testCsReturnCollectRepository->getRecordStatus();
        $form = $this->createForm(DateReturnType::class);
        $form->handleRequest($request);
        $form2 = $this->createForm(ReturnReasonType::class);
        $form2->handleRequest($request);
        if ($request->request->get('date')) {
            $dateReturn = $request->request->get('date');
            $session->set('dateTime', date("Y-m-d", strtotime($dateReturn)));
        }
        // merchant_billing_delivery
        $merchantBillingDeliveryObj = $testCsReturnCollectRepository->getMailCodeByStatus($session->get('dateTime'));
        if ($merchantBillingDeliveryObj != null) {
            $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
            // merchant_billing
            $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                $merchantBillingDeliveryObj[0]['paymentInvoice']);
            foreach ($merchantBillingObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'orderphoneno') {
                        $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                    }
                }
            }
            // merchant_billing_detail
            $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                $merchantBillingDeliveryObj[0]['paymentInvoice']);
            if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                // Global_Transporter
                $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
            } else {
                $transporterObj = null;
            }
            // global_orderstatus
            $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
            // global_authen
            $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
            // merchant_config
            $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
            // parcel_temp_capture
            $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
            // tracking_log
            $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
            // parcel_member

            $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
            foreach ($parcelMemberObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'phoneregis') {
                        $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                    }
                }
            }
            // postinfo_zipcode
            $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
            $resultData = array(
                'paymentInvoice' => $merchantBillingDeliveryObj,
                'merchantBilling' => $merchantBillingObj,
                'merchantDetail' => $merchantDetailObj,
                'COD' => $merchantBillingDeliveryCODResult,
                'transporter' => $transporterObj,
                'globalOrderStatus' => $globalOrderStatusObj,
                'parcelTemp' => $parcelTempObj,
                'trackingLog' => $trackingLogObj,
                'globalAuthen' => $globalAuthenObj->getFname(),
                'merchantConfig' => $merchantConfigObj,
                'parcelMember' => $parcelMemberObj,
                'zipCode' => $zipCodeObj
            );
        }

        return $this->render('parcel/csreturn.html.twig', [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'resultData' => $resultData,
            'status' => $statusObj
        ]);
    }

    /**
     * @Route("/csreturn_collect", name="csreturn_collect")
     */
    public function csReturn_collect(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository,
        TestCsReturnCollectRepository $testCsReturnCollectRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $resultData = [];
        $faker = Factory::create();
        $statusObj = $testCsReturnCollectRepository->getMailCodeAndIdByMerTypeAndDate();
        $count = count($statusObj);
        $randomMailCode = $randomMailCode = $faker->randomElement($statusObj);
        // merchant_billing_delivery
        $merchantBillingDeliveryObj = $testCsReturnCollectRepository->getMailCodeAndIdByMailCode($randomMailCode["mailcode"]);
        if ($merchantBillingDeliveryObj != null) {
            $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
            // merchant_billing
            $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                $merchantBillingDeliveryObj[0]['paymentInvoice']);
            foreach ($merchantBillingObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'orderphoneno') {
                        $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                    }
                }
            }
            // merchant_billing_detail
            $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                $merchantBillingDeliveryObj[0]['paymentInvoice']);
            if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                // Global_Transporter
                $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
            } else {
                $transporterObj = null;
            }
            // global_orderstatus
            $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
            // global_authen
            $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
            // merchant_config
            $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
            // parcel_temp_capture
            $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($randomMailCode["mailcode"]);
            // tracking_log
            $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($randomMailCode["mailcode"]);
            // parcel_member

            $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
            foreach ($parcelMemberObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'phoneregis') {
                        $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                    }
                }
            }
            // postinfo_zipcode
            $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
            $resultData = array(
                'paymentInvoice' => $merchantBillingDeliveryObj,
                'merchantBilling' => $merchantBillingObj,
                'merchantDetail' => $merchantDetailObj,
                'COD' => $merchantBillingDeliveryCODResult,
                'transporter' => $transporterObj,
                'globalOrderStatus' => $globalOrderStatusObj,
                'parcelTemp' => $parcelTempObj,
                'trackingLog' => $trackingLogObj,
                'globalAuthen' => $globalAuthenObj->getFname(),
                'merchantConfig' => $merchantConfigObj,
                'parcelMember' => $parcelMemberObj,
                'zipCode' => $zipCodeObj
            );
        }

        return $this->render('parcel/cs_return_collect.html.twig', [
            'count' => $count,
            'resultData' => $resultData
        ]);
    }

    /**
     * @Route("/phonesearch", name="phonesearch")
     */
    public function phoneInput(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        ParcelTempDataRepository $parcelTempDataRepository,
        PcComApprovedRepository $pcComApprovedRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $form2 = $this->createForm(RemarksType::class);
        $form2->handleRequest($request);
        $resultData = [];
        $merchantBillingSObj = [];
        if ($request->query->get('search') != null) {
            if (strlen($request->query->get('search')) == 10) {
                $merchantBillingSObj = $merchantBillingRepository->getMailCodeAndDataBySearch($request->query->get('search'));
                if ($merchantBillingSObj == null) {
                    $search = $this->ZeroToDoubleSix($request->query->get('search'));
                    $merchantBillingSObj = $merchantBillingRepository->getMailCodeAndDataBySearch($search);
                    if ($merchantBillingSObj == null) {
                        $this->addFlash('error', 'ไม่พบเบอร์โทรศัพท์ กรุณากรอกใหม่อีกครั้ง !');
                        return $this->redirect($this->generateUrl('phonesearch'));
                    }
                }
            } else {
                // return $this->redirect($this->generateUrl('phonesearch'));
                $merchantBillingSObj = $merchantBillingRepository->getMailCodeAndDataBySearchName($request->query->get('search'));
            }
            foreach ($merchantBillingSObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'orderphoneno') {
                        $merchantBillingSObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                    }
                    if ($k === 'parcelMemberId') {
                        if ($merchantBillingSObj[$key]['parcelMemberId'] != null) {
                            $parcelMember = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($v);
                            $merchantBillingSObj[$key]['parcelMemberId'] = $parcelMember[0]['firstname'] . ' ' . $parcelMember[0]['lastname'];
                        }
                    }
                    if ($k === 'transporterId') {
                        if ($merchantBillingSObj[$key]['transporterId'] == 0) {
                            $merchantBillingSObj[$key]['transporterId'] = null;
                        } else {
                            $globalTransporter = $globalTransporterRepository->getTransporterNameByTransportId($v);
                            $merchantBillingSObj[$key]['transporterId'] = $globalTransporter[0]['transporterName'];
                        }
                    }
                }
            }
        }

        if ($request->query->get('takeorderby') && $request->query->get('invoice')) {
            $merchantBillingDeliveryObj = $merchantBillingDeliveryRepository->getInvoiceAndTakeOrderByByTakeOrderByAndInvoice($request->query->get('takeorderby'), $request->query->get('invoice'));
            if ($merchantBillingDeliveryObj != null) {
                $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
                // merchant_billing
                $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($merchantBillingObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'orderphoneno') {
                            $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // merchant_billing_detail
                $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                    // Global_Transporter
                    $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
                } else {
                    $transporterObj = null;
                }
                // global_orderstatus
                $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
                // global_authen
                $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
                // merchant_config
                $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
                // parcel_temp_capture
                $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // tracking_log
                $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // parcel_member
                $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
                // parcel_Temp_Data
                $parcelTempDataObj = $parcelTempDataRepository->getDataByConsignmentNo($merchantBillingDeliveryObj[0]['mailcode']);
                foreach ($parcelTempDataObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'operator') {
                            $parcelTempDataObj[$key]['operator'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // pc_com_approved
                $pcComAppObj = $pcComApprovedRepository->getTransferAmtByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($parcelMemberObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'phoneregis') {
                            $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // postinfo_zipcode
                $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
                $resultData = array(
                    'paymentInvoice' => $merchantBillingDeliveryObj,
                    'merchantBilling' => $merchantBillingObj,
                    'merchantDetail' => $merchantDetailObj,
                    'COD' => $merchantBillingDeliveryCODResult,
                    'transporter' => $transporterObj,
                    'globalOrderStatus' => $globalOrderStatusObj,
                    'parcelTemp' => $parcelTempObj,
                    'trackingLog' => $trackingLogObj,
                    'globalAuthen' => $globalAuthenObj->getFname(),
                    'merchantConfig' => $merchantConfigObj,
                    'parcelMember' => $parcelMemberObj,
                    'zipCode' => $zipCodeObj,
                    'parcelTempData' => $parcelTempDataObj,
                    'pcComApp' => $pcComAppObj
                );
            }
        }

        return $this->render('parcel/searchinput.html.twig', [
            'form2' => $form2->createView(),
            'fields' => $merchantBillingSObj,
            'resultData' => $resultData,
        ]);
    }

    /**
     * @Route("/sender_search", name="phonesender")
     */
    public function senderSearch(
        Request $request,
        EntityManagerInterface $entityManager,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantBillingRepository $merchantBillingRepository,
        MerchantBillingDetailRepository $merchantBillingDetailRepository,
        ParcelTempDataRepository $parcelTempDataRepository,
        PcComApprovedRepository $pcComApprovedRepository,
        GlobalTransporterRepository $globalTransporterRepository,
        MerchantConfigRepository $merchantConfigRepository,
        ParcelTempCaptureRepository $parcelTempCaptureRepository,
        TrackingLogRepository $trackingLogRepository,
        ParcelMemberRepository $parcelMemberRepository,
        PostInfoZipcodesRepository $postInfoZipcodesRepository,
        GlobalOrderStatusRepository $globalOrderStatusRepository,
        GlobalAuthenRepository $globalAuthenRepository,
        TestAllParcelRepository $allParcelRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $form2 = $this->createForm(RemarksType::class);
        $form2->handleRequest($request);
        $resultData = [];
        $merchantBillingSObj = [];
        if ($request->query->get('senderId') != null && $request->query->get('date') != null) {
            $startDate = date("Y-m-d", strtotime($request->query->get('date') . "-30 days"));
            $endDate = date('Y-m-d', strtotime($request->query->get('date')));
            $merchantBillingSObj = $allParcelRepository->getDataMailCodeAndRecipientPhone($request->query->get('senderId'), $startDate, $endDate);

            if ($merchantBillingSObj == null) {
                $this->addFlash('error', 'ไม่พบไอดีผู้ส่ง กรุณากรอกใหม่อีกครั้ง !');
                return $this->redirect($this->generateUrl('phonesender'));
            }
//            dd($merchantBillingSObj);
            foreach ($merchantBillingSObj as $key => $val) {
                foreach ($val as $k => $v) {
                    if ($k === 'orderphoneno') {
                        $merchantBillingSObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                    }
                    if ($k === 'parcelMemberId') {
                        if ($merchantBillingSObj[$key]['parcelMemberId'] != null) {
                            $parcelMember = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($v);
                            $merchantBillingSObj[$key]['parcelMemberId'] = $parcelMember[0]['firstname'] . ' ' . $parcelMember[0]['lastname'];
                        }
                    }
                    if ($k === 'transporterId') {
                        if ($merchantBillingSObj[$key]['transporterId'] == 0) {
                            $merchantBillingSObj[$key]['transporterId'] = null;
                        } else {
                            $globalTransporter = $globalTransporterRepository->getTransporterNameByTransportId($v);
                            $merchantBillingSObj[$key]['transporterId'] = $globalTransporter[0]['transporterName'];
                        }
                    }
                }
            }
        }

        if ($request->query->get('takeorderby') && $request->query->get('invoice')) {
            $merchantBillingDeliveryObj = $merchantBillingDeliveryRepository->getInvoiceAndTakeOrderByByTakeOrderByAndInvoice($request->query->get('takeorderby'), $request->query->get('invoice'));
            if ($merchantBillingDeliveryObj != null) {
                $merchantBillingDeliveryCODResult = ($merchantBillingDeliveryObj[0]['codPrice'] + $merchantBillingDeliveryObj[0]['expenseDiscount']);
                // merchant_billing
                $merchantBillingObj = $merchantBillingRepository->getOrderNameAndOrderPhoneNoByInvoiceAndTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($merchantBillingObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'orderphoneno') {
                            $merchantBillingObj[$key]['orderphoneno'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // merchant_billing_detail
                $merchantDetailObj = $merchantBillingDetailRepository->getProductNameAndProductOrderByTakeOrderByAndInvoice($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                if ($merchantBillingDeliveryObj[0]['transporterId'] != null) {
                    // Global_Transporter
                    $transporterObj = $globalTransporterRepository->find($merchantBillingDeliveryObj[0]['transporterId']);
                } else {
                    $transporterObj = null;
                }
                // global_orderstatus
                $globalOrderStatusObj = $globalOrderStatusRepository->getStatusNameTHByOrderStatus($merchantBillingObj[0]['orderstatus']);
                // global_authen
                $globalAuthenObj = $globalAuthenRepository->find($merchantBillingObj[0]['adminid']);
                // merchant_config
                $merchantConfigObj = $merchantConfigRepository->getMerchantNameAndMerTypeByByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby']);
                // parcel_temp_capture
                $parcelTempObj = $parcelTempCaptureRepository->getImgUrlByByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // tracking_log
                $trackingLogObj = $trackingLogRepository->getRemarksAndUserByMailCode($merchantBillingDeliveryObj[0]['mailcode']);
                // parcel_member
                $parcelMemberObj = $parcelMemberRepository->getFirstNameAndLastNameAndPhoneByMemberId($merchantBillingObj[0]['parcelMemberId']);
                // parcel_Temp_Data
                $parcelTempDataObj = $parcelTempDataRepository->getDataByConsignmentNo($merchantBillingDeliveryObj[0]['mailcode']);
                foreach ($parcelTempDataObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'operator') {
                            $parcelTempDataObj[$key]['operator'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // pc_com_approved
                $pcComAppObj = $pcComApprovedRepository->getTransferAmtByTakeOrderBy($merchantBillingDeliveryObj[0]['takeorderby'],
                    $merchantBillingDeliveryObj[0]['paymentInvoice']);
                foreach ($parcelMemberObj as $key => $val) {
                    foreach ($val as $k => $v) {
                        if ($k === 'phoneregis') {
                            $parcelMemberObj[$key]['phoneregis'] = $this->doubleSix2Zero($v);
                        }
                    }
                }
                // postinfo_zipcode
                $zipCodeObj = $postInfoZipcodesRepository->getDhlSlaByDistrictCode($merchantBillingObj[0]['districtcode']);
                $resultData = array(
                    'paymentInvoice' => $merchantBillingDeliveryObj,
                    'merchantBilling' => $merchantBillingObj,
                    'merchantDetail' => $merchantDetailObj,
                    'COD' => $merchantBillingDeliveryCODResult,
                    'transporter' => $transporterObj,
                    'globalOrderStatus' => $globalOrderStatusObj,
                    'parcelTemp' => $parcelTempObj,
                    'trackingLog' => $trackingLogObj,
                    'globalAuthen' => $globalAuthenObj->getFname(),
                    'merchantConfig' => $merchantConfigObj,
                    'parcelMember' => $parcelMemberObj,
                    'zipCode' => $zipCodeObj,
                    'parcelTempData' => $parcelTempDataObj,
                    'pcComApp' => $pcComAppObj
                );
            }
        }

        return $this->render('parcel/searchsender.html.twig', [
            'form2' => $form2->createView(),
            'fields' => $merchantBillingSObj,
            'resultData' => $resultData,
        ]);
    }

    /**
     * @Route("/return_check", name="return_check")
     */
    public function return_check(
        Request $request,
        EntityManagerInterface $entityManager,
        TestEcomToPreakLogRepository $testEcomToPreakLogRepository,
        MerchantBillingDeliveryRepository $billingDeliveryRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $form = $this->createForm(MerchantBillingDeliveryType::class);
        $form->handleRequest($request);
        $testComData = [];
        if ($form->get('mailcode')->getData() && $form->isSubmitted()) {
            if (strlen($form->get('mailcode')->getData()) <= 12 || $this->endsWith($form->get('mailcode')->getData(), "TH")) {
                $testComData = $testEcomToPreakLogRepository->getDataEcomByMailCode($form->get('mailcode')->getData());
                if ($testComData == null) {
                    $testComData = $billingDeliveryRepository->getBillingDetailByByMailCode($form->get('mailcode')->getData());
                }
            } else {
                $this->addFlash('error', 'กรุณากรอกข้อมูลให้ถูกต้อง');
                return $this->redirect($this->generateUrl('ecom_search'));
            }
        }

        return $this->render('parcel/ecom.html.twig', [
            'form' => $form->createView(),
            'resultData' => $testComData
        ]);
    }

    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }

    public function doubleSix2Zero($phoneNO)
    {
        $pattern = '/^66\d{9}$/';
        $phoneNO = trim($phoneNO);
        if (preg_match($pattern, $phoneNO)) {
            $arr = str_split($phoneNO);
            if (isset($arr[0], $arr[1])) {
                if ($arr[0] . $arr[1] == '66') {
                    $phoneNO = '0';
                    for ($i = 2; $i < count($arr); $i++) {
                        $phoneNO .= $arr[$i];
                    }
                }
            }
        }
        return $phoneNO;
    }

    public function ZeroToDoubleSix($phoneNO)
    {
        $arr = str_split($phoneNO);
        if (isset($arr[0])) {
            if ($arr[0] == '0') {
                $phoneNO = '66';
                for ($i = 1; $i < count($arr); $i++) {
                    $phoneNO .= $arr[$i];
                }
            }
        }
        return $phoneNO;
    }

    /**
     * @Route("/remark/addsubremark", name="addsubremark")
     */
    public function addSubRemark(
        Request $request,
        EntityManagerInterface $entityManager,
        TestCsReturnReasonSubRepository $testCsReturnReasonSubRepository
    )
    {
        $data = $testCsReturnReasonSubRepository->getReasonSubByReasonId($request->query->get('reasonId'));
        return $this->json($data);
    }

    /**
     * @Route("/remark/submit", name="remark")
     */
    public function Remarks(
        Request $request,
        EntityManagerInterface $entityManager,
        TestCSRemarksStatusRepository $testCSRemarksStatusRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $remarks = $request->query->get('remarks');
        $remarkStatus = $testCSRemarksStatusRepository->find($remarks['status']);
        if ($remarks != null) {
            $csLogMailCode = new CsLogMailcode();
            $csLogMailCode->setMailcode($remarks['mailCode']);
            $csLogMailCode->setStatus($remarks['process']);
            $csLogMailCode->setPublicStatus($remarkStatus->getStatusName());
            $csLogMailCode->setRemarks($remarks['note']);
            $csLogMailCode->setUser($this->getUser()->getDisplayName());
            $csLogMailCode->setTstamp(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $entityManager->persist($csLogMailCode);
        }
        $entityManager->flush();
        return $this->redirect($this->generateUrl('parcel'));
    }

    /**
     * @Route("/return/submit", name="return_submit")
     */
    public function ReturnSubmit(
        Request $request,
        EntityManagerInterface $entityManager
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $return_reason = $request->query->get('return_reason');
        if ($return_reason != null) {
            $csReturnLog = new TestCsReturnLog();
            $csReturnLog->setMailcode($return_reason['mailCode']);
            $csReturnLog->setReasonId($return_reason['reason']);
            $csReturnLog->setSubReasonId($request->query->get('subReasonCode'));
            $csReturnLog->setCatId($return_reason['cat']);
            $csReturnLog->setReasonRemarks($return_reason['note']);
            $csReturnLog->setLogUser($this->getUser()->getDisplayName());
            $csReturnLog->setLogTimestamp(new \DateTime("now", new \DateTimeZone('Asia/Bangkok')));
            $entityManager->persist($csReturnLog);
        }
        $entityManager->flush();
        if ($request->query->get('page') == "queue_search") {
            return $this->redirect($this->generateUrl('queuesearch'));
        } else {
            return $this->redirect($this->generateUrl('csreturn'));
        }
    }

    /**
     * @Route("/return_collect/submit", name="return_collect_submit")
     */
    public function ReturnCollectSubmit(
        Request $request,
        EntityManagerInterface $entityManager,
        TestCsReturnCollectRepository $testCsReturnCollectRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $return_reason = $request->query->get('return_reason');
        if ($return_reason != null) {
            $csReturnCollect = $testCsReturnCollectRepository->find($return_reason['recordId']);
            $csReturnCollect->setCatId($return_reason['cat']);
            $entityManager->flush();
        }
        return $this->redirect($this->generateUrl('csreturn_collect'));
    }

    /**
     * @Route("/delete", name="delete")
     */
    public function delete()
    {
        $session = new Session();
        $session->remove('dateTime');
        return $this->redirect($this->generateUrl('csreturn'));
    }
}
