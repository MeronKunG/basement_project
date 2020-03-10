<?php

namespace App\Controller;

use App\Form\MerchantConfigType;
use App\Repository\MerchantBillingDeliveryRepository;
use App\Repository\MerchantConfigRepository;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdjustmentController extends AbstractController
{
    /**
     * @Route("/q-adjustment", name="adjustment")
     */
    public function Adjustment(
        Request $request,
        MerchantBillingDeliveryRepository $merchantBillingDeliveryRepository,
        MerchantConfigRepository $merchantConfigRepository
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $session = new Session();
        $result = array();
        $merchantData = array();
        $form = $this->createForm(MerchantConfigType::class);
        $form->handleRequest($request);
        if ($form->get('submit')->isSubmitted()) {
            if($form->get('merType')->getData() == 'holding') {
                $session->set('type', array('holding', 'hybrid'));
            } else {
                $session->set('type', array('parcel'));
            }
            $session->set('dateTime', $form->get('dateTime')->getData());
            $session->set('billing', $form->get('billing')->getData());
            $session->set('check', $form->get('check')->getData());
        }
        if ($session->get('type') != null) {
            if ($session->get('check') == 'yes') {
                if($session->get('billing') == 'codnormal') {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'));
                } else {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'));
                }
            } else {
                if($session->get('billing') == 'codnormal') {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'));
                } else {
                    $dataFormAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'));
                }
            }
            foreach ($dataFormAdjustment as $element) {
                $result[$element['takeorderby']][] = $element;
            }
            $listTakeOrderBy = array_keys($result);
            $resultGenerateData = $merchantConfigRepository->getMerchantNameByByTakeOrderByArray($listTakeOrderBy);
            foreach ($resultGenerateData as $t1) {
                $merchantData[$t1['merchantname'] . '(' . count($result[$t1['takeorderby']]) . ')'] = $t1['takeorderby'];
            }
            $form2 = $this->createFormBuilder()
                ->add('shop', ChoiceType::class, [
                    'choices' => $merchantData,
                    'expanded' => true,
                    'multiple' => true,
                ])
                ->add('hiddenName', HiddenType::class)
                ->add('save', SubmitType::class, ['label' => 'Select'])
                ->getForm();

            if ($session->get('check') == 'yes') {
                if($session->get('billing') == 'codnormal') {
                    $dataAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'), $session->get('shopId'));
                } else {
                    $dataAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNotNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'), $session->get('shopId'));
                }
            } else {
                if($session->get('billing') == 'codnormal') {
                    $dataAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), array('cod', 'normal'), $session->get('shopId'));
                } else {
                    $dataAdjustment = $merchantBillingDeliveryRepository->getMailCodeBySessionAdjustmentNull(date_format($session->get('dateTime'),
                        "Y-m-d"), $session->get('type'), $session->get('billing'), $session->get('shopId'));
                }
            }
            return $this->render('adjustment/index.html.twig', [
                'form' => $form->createView(),
                'form2' => $form2->createView(),
                'shopNum' => count($dataAdjustment)
            ]);
        }
        return $this->render('adjustment/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/shop/save", name="shop_save")
     */
    public function Remarks(
        Request $request
    ) {
        date_default_timezone_set("Asia/Bangkok");
        $session = new Session();
        $shop = $request->request->get('form');
        if (!empty($shop['shop'])) {
            $session->set('shopId', $shop['shop']);
        }
        return $this->redirect($this->generateUrl('adjustment'));
    }
}
