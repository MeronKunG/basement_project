<?php

namespace App\Controller;

use App\Repository\MerchantBillingDeliveryRepository;
use App\Repository\TestQuickLinkDetectionRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;

class DetectionController extends AbstractController
{
    /**
     * @Route("/detection", name="detection")
     */
    public function detection(
        Request $request,
        TestQuickLinkDetectionRepository $linkDetectionRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $detectionMailCodeObj = [];
        $detectionObj = [];
        $detectionObj2 = $linkDetectionRepository->getDataByDataIsNull();
        $test2 = [];
        foreach ($detectionObj2 as $key => $val) {
            $detectionMailCodeObj[$val['captureDate']->format('d-m-Y')][] = $val;
        }
        foreach ($detectionMailCodeObj as $key => $val) {
            $detectionMailCodeObj[$key] = ["date" => $key, "amount" => count($val)];
        }

        usort($detectionMailCodeObj, function ($item1, $item2) {
            return strtotime($item2['date']) <=> strtotime($item1['date']);
        });

        if ($request->query->get('search') != null) {
            if ($request->query->get('search') == "empty") {
                return $this->redirect($this->generateUrl('detection'));
            }
            $date = date("Y-m-d", strtotime($request->query->get('search')));
            $detectionObj = $linkDetectionRepository->getDataBySearch($date);

        }
        return $this->render('detection/detection.html.twig', [
            'dataObj' => $detectionObj,
            'dataMailCodeObj' => $detectionMailCodeObj,
            'test' => $test2
        ]);
    }

    /**
     * @Route("/detection_complete", name="detection_complete")
     */
    public function detection_complete(
        Request $request,
        TestQuickLinkDetectionRepository $linkDetectionRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $detectionMailCodeObj = [];
        $detectionObj = [];
        $detectionObj2 = $linkDetectionRepository->getDataByDataIsNullBeActionOnZero("COMPLETE");
        $test2 = [];
        foreach ($detectionObj2 as $key => $val) {
            $detectionMailCodeObj[$val['captureDate']->format('d-m-Y')][] = $val;
        }
        foreach ($detectionMailCodeObj as $key => $val) {
            $detectionMailCodeObj[$key] = ["date" => $key, "amount" => count($val)];
        }

        usort($detectionMailCodeObj, function ($item1, $item2) {
            return strtotime($item2['date']) <=> strtotime($item1['date']);
        });

        if ($request->query->get('search') != null) {
            if ($request->query->get('search') == "empty") {
                return $this->redirect($this->generateUrl('detection_complete'));
            }
            $date = date("Y-m-d", strtotime($request->query->get('search')));
            $detectionByDateObj = $linkDetectionRepository->getDataBySearch2OnZero($date, "COMPLETE");


            foreach ($detectionByDateObj as $key => $val) {
                $detectionByDateObj[$key]["captureDate"] = $detectionByDateObj[$key]["captureDate"]->format('d-m-Y');
                $test2[$val["merchantname"]][] = $val;
            }

            foreach ($test2 as $key => $val) {
                $test2[$key] = ["shopName" => $key, "count" => count($val), "date" => $request->query->get('search')];
                foreach ($val as $k => $v) {
                    $test2[$key]["mailcode"][] = $v["mailcode"];
                }
            }

            if ($request->query->get('merchantName') != null) {
                if ($request->query->get('merchantName') == "empty") {
                    return $this->redirect($this->generateUrl('detection_complete', array('search' => $request->query->get('search'))));
                }
                foreach ($detectionByDateObj as $key => $val) {
                    if ($val["merchantname"] === $request->query->get('merchantName')) {
                        $detectionObj[] = $val;
                    }
                }
//                $maxItemPerPage = count($detectionObj);
            }
        }
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
            $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
            $dataJson = $serializer->decode($request->request->get("mailCode"), 'json');
            $detectData = $linkDetectionRepository->findOneBy(array("mailcode" => $dataJson[0]));
            $detectData->setClearStatus($dataJson[1]);
            $detectData->setUpdatedBy($this->getUser()->getDisplayName());
            $entityManager->flush();
            return $this->redirect($this->generateUrl('detection_complete', array('search' => $request->query->get('search'), 'merchantName' => $request->query->get('merchantName'))));
        }
        return $this->render('detection/detect_complete.html.twig', [
            'dataObj' => $detectionObj,
            'dataMailCodeObj' => $detectionMailCodeObj,
            'test' => $test2
        ]);
    }

    /**
     * @Route("/detection_process", name="detection_process")
     */
    public function detection_process(
        Request $request,
        TestQuickLinkDetectionRepository $linkDetectionRepository
    )
    {
        date_default_timezone_set("Asia/Bangkok");
        $detectionMailCodeObj = [];
        $detectionObj = [];
        $detectionObj2 = $linkDetectionRepository->getDataByDataIsNullBeActionOnZero("ON PROCESS");
        $test2 = [];
        foreach ($detectionObj2 as $key => $val) {
            $detectionMailCodeObj[$val['captureDate']->format('d-m-Y')][] = $val;
        }
        foreach ($detectionMailCodeObj as $key => $val) {
            $detectionMailCodeObj[$key] = ["date" => $key, "amount" => count($val)];
        }


        usort($detectionMailCodeObj, function ($item1, $item2) {
            return strtotime($item2['date']) <=> strtotime($item1['date']);
        });

        if ($request->query->get('search') != null) {
            if ($request->query->get('search') == "empty") {
                return $this->redirect($this->generateUrl('detection_process'));
            }
            $date = date("Y-m-d", strtotime($request->query->get('search')));
            $detectionByDateObj = $linkDetectionRepository->getDataBySearch2OnZero($date, "ON PROCESS");


            foreach ($detectionByDateObj as $key => $val) {
                $detectionByDateObj[$key]["captureDate"] = $detectionByDateObj[$key]["captureDate"]->format('d-m-Y');
                $test2[$val["merchantname"]][] = $val;
            }

            foreach ($test2 as $key => $val) {
                $test2[$key] = ["shopName" => $key, "count" => count($val), "date" => $request->query->get('search')];
                foreach ($val as $k => $v) {
                    $test2[$key]["mailcode"][] = $v["mailcode"];
                }
            }

            if ($request->query->get('merchantName') != null) {
                if ($request->query->get('merchantName') == "empty") {
                    return $this->redirect($this->generateUrl('detection_process', array('search' => $request->query->get('search'))));
                }
                foreach ($detectionByDateObj as $key => $val) {
                    if ($val["merchantname"] === $request->query->get('merchantName')) {
                        $detectionObj[] = $val;
                    }
                }
            }
        }
        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();
            $serializer = new Serializer(array(new GetSetMethodNormalizer()), array('json' => new JsonEncoder()));
            $dataJson = $serializer->decode($request->request->get("mailCode"), 'json');
            $detectData = $linkDetectionRepository->findOneBy(array("mailcode" => $dataJson[0]));
            $detectData->setClearStatus($dataJson[1]);
            $detectData->setUpdatedBy($this->getUser()->getDisplayName());
            $entityManager->flush();
            return $this->redirect($this->generateUrl('detection_process', array('search' => $request->query->get('search'), 'merchantName' => $request->query->get('merchantName'))));
        }
        return $this->render('detection/detect_process.html.twig', [
            'dataObj' => $detectionObj,
            'dataMailCodeObj' => $detectionMailCodeObj,
            'test' => $test2
        ]);
    }
}
