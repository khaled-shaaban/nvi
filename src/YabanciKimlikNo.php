<?php
namespace Teknomavi\NVI;

use Teknomavi\Common\Text;
use Teknomavi\NVI\Service\KPSPublic\KPSPublic;
use Teknomavi\NVI\Service\KPSPublic\YabanciKimlikNoDogrulaRequest;

class YabanciKimlikNo
{
    /**
     * Kimlik bilgileri verilen kişinin Yabanci Numarasının doğruluğunu kontrol eder.
     *
     * @param int    $KimlikNo Yabanci Kimlik Numarası
     * @param string $ad         Ad
     * @param string $soyad      Soyad
     * @param int    $dogumYili  4 basamaklı doğum yılı ( Örn: 1981 )
     * @param int    $dogumAy    ( Örn: 6 )
     * @param int    $dogumGun   ( Örn: 24 )
     *
     * @throws Exception\InvalidTCKimlikNo
     * @return bool
     */
    public function dogrula($KimlikNo, $ad, $soyad, $dogumYili, $dogumAy, $dogumGun)
    {
        if (!$this->kontrolEt($KimlikNo)) {
            throw new Exception\InvalidTCKimlikNo($KimlikNo);
        }
        $client = new KPSPublic('https://tckimlik.nvi.gov.tr/Service/KPSPublicYabanciDogrula.asmx?WSDL');
        $request = new YabanciKimlikNoDogrulaRequest();
        // Webservise gönderilen talepte TC Kimlik Numarası integer olmalı.
        $request->KimlikNo = $KimlikNo * 1;

        $request->Ad = Text::strToUpper($ad);
        $request->Soyad = Text::strToUpper($soyad);

        $request->DogumYil = $dogumYili * 1;
        $request->DogumAy = $dogumAy * 1;
        $request->DogumGun = $dogumGun * 1;

        $response = $client->yabanciKimlikNoDogrula($request);

        return $response->YabanciKimlikNoDogrulaResult;
    }

    /**
     * Verilen numarayı T.C. Kimlik Numarası algoritması ile kontrol eder
     *
     * @see http://tr.wikipedia.org/wiki/Türkiye_Cumhuriyeti_Kimlik_Numarası
     *
     * @param mixed $number
     *
     * @return bool
     */
    public function kontrolEt($number)
    {
        $number = (string) $number;
        if (strlen($number) != 11) {
            return false;
        }
        $l = str_split($number);
        if (($l[0] + $l[1] + $l[2] + $l[3] + $l[4] + $l[5] + $l[6] + $l[7] + $l[8] + $l[9]) % 10 != $l[10]) {
            return false;
        }
        if ((($l[0] + $l[2] + $l[4] + $l[6] + $l[8]) * 8) % 10 != $l[10]) {
            return false;
        }

        return true;
    }
}
