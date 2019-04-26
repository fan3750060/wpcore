<?php

namespace Thinbus\Test;

use Math_BigInteger;
use PHPUnit\Framework\TestCase;
use Thinbus\ThinbusSrp;
use Thinbus\ThinbusSrpClient;

/**
 * This subclass lets use override the random 'b' value and constant 'k' value with those seen in a debugger running the js+java thinbus tests.
 */
class NotRandomSrp extends ThinbusSrp
{

    protected $notRandomNumber;

    function setNotRandom($nr)
    {
        $this->notRandomNumber = new Math_BigInteger($nr, 16);
    }

    function createRandomBigIntegerInRange($n)
    {
        return $this->notRandomNumber;
    }
}

/**
 * This subclass lets use override the random 'b' value and constant 'k' value with those seen in a debugger running the js+java thinbus tests.
 */
class NotRandomSrpClient extends ThinbusSrpClient
{

    protected $notRandomNumber;

    function setNotRandom($nr)
    {
        $this->notRandomNumber = new Math_BigInteger($nr, 16);
    }

    function createRandomBigIntegerInRange($n)
    {
        return $this->notRandomNumber;
    }
    
}

class ThibusTest extends TestCase
{

    private $Srp;

    private $SrpClient;
    
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
        $g_base10str = "2";
        $k_base16str = "1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a";
        
        $this->Srp = new NotRandomSrp($N_base10str, $g_base10str, $k_base16str, "sha256");
        
        $this->SrpClient = new NotRandomSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
        
        $this->SrpClient->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->Srp = null;
        
        parent::tearDown();
    }

    /**
     * There was a bug in the old version of the BigInteger library which gave negative $S numbers for the server math.   
     */
    public function testBigIntegerServerMaths() {
        $N = new Math_BigInteger("19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107");
        $g = new Math_BigInteger("2");
        $k = new Math_BigInteger("1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a", 16);
        $x = new Math_BigInteger("5155132629181267711731172957623683958757742038141659854754010317958234992195");
        $u = new Math_BigInteger("6117132599428a87abb6b17b4325278d9c6a9fb15c03a5c935a423557d52533f", 16);
        $a = new Math_BigInteger("19361658154478038029563330608883713601241650239358160672724489109538144633930458124195627273528747418345389938502589867953675573095143032573371565016771668475967736317546032047197889287820636050588623914951695103255644749159959105130076313688260609470936740451350774611633258729707900506");
        $B = new Math_BigInteger("6b685cf47daced8ce9c9435840bf41af63ee909bb2af86731762a880e2c844d72fbb16192229960ee96ed1221c926cffc50247f89add6f363346a6c8e404bfd6d683b0cc9db1f5810e775d4bdad2baae804d64ef62106b4155b8ffb6cd066b2ca4ebadd435032495ecdac00a273dd58cb6cca84350c03e", 16);
        
        $exp = $u->multiply($x)->add($a);
        $tmp = $g->modPow($x, $N)->multiply($k);
        $delete = $B->subtract($tmp);
        
        $S = $delete->modPow($exp, $N);
        
        $expectedS = new Math_BigInteger("10835297006612231441535135813189185780216932496669890580376452737672007848862630182854031090939738824764923980738304601400127638808530720929086886615907405340530126178661080753590589465090593207825822232194035559316523834699995951753403257297725124970828786492617440453059425162658876658");
        
        $this->assertEquals($S->abs(), $S);
        
        $this->assertEquals($expectedS, $S);
    }
    
    /**
     * There was a different bug in the old version of the Math_BigInteger library which gave negative $S numbers for the client math.
     */
    public function testBigIntegerMaths2() {
        $one = new Math_BigInteger("1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a", 16);
        $two = new Math_BigInteger("6117132599428a87abb6b17b4325278d9c6a9fb15c03a5c935a423557d52533f", 16);
        $neg = $one->subtract($one);
        $N = new Math_BigInteger("19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107");
        $g = new Math_BigInteger("2");
        $modP = $neg->modPow($g, $N);
        $this->assertEquals($modP->abs(), $modP); 
    }
    
    /**
     * Unfortunately the PEAR Math_BigInteger library in the year 2017 doesn't have a getLength() method like the Java library so I had to code one.  
     */
    public function testBigIntegerPrecision() {
        $expectedValues = array(
                array(1,	"1"),
                array(8,	"10000000"),
                array(15,	"100000000000000"),
                array(22,	"1000000000000000000000"),
                array(29,	"10000000000000000000000000000"),
                array(36,	"100000000000000000000000000000000000"),
                array(43,	"1000000000000000000000000000000000000000000"),
                array(50,	"10000000000000000000000000000000000000000000000000"),
                array(57,	"100000000000000000000000000000000000000000000000000000000"),
                array(64,	"1000000000000000000000000000000000000000000000000000000000000000"),
                array(71,	"10000000000000000000000000000000000000000000000000000000000000000000000"),
                array(78,	"100000000000000000000000000000000000000000000000000000000000000000000000000000"),
                array(85,	"1000000000000000000000000000000000000000000000000000000000000000000000000000000000000"),
                array(92,	"10000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000"),
                array(99,	"100000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000"),
                array(106,	"1000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000")
            );
        
        $N_base10str = "255";
        $g_base10str = "1";
        $k_base16str = "1";
        
        $Srp = new ThinbusSrp($N_base10str, $g_base10str, $k_base16str, "sha256");
        
        for($i = 0; $i < 16; ++$i) {
            $expected = $expectedValues[$i];
            $expectedPrecision = $expected[0];
            $base2Str = $expected[1];
            $bi = new Math_BigInteger($base2Str, 2);
            $actualPrecision = $Srp->getPrecision($bi);
            $this->assertEquals($expectedPrecision, $actualPrecision);
        }
    }
    
    public function testCreateRandomBigIntegerInRange() {
        
        $N_base10str = "255";
        $N = new Math_BigInteger($N_base10str, 10);
        $g_base10str = "1";
        $k_base16str = "1";
        
        $Srp = new ThinbusSrp($N_base10str, $g_base10str, $k_base16str, "sha256");
        
        $values = array();
        for( $i = 0; $i < 256; ++$i) {
            $values[$i] = 0;
        }
        for( $i = 0; $i < 1e3; ++$i) {
            $r = $Srp->createRandomBigIntegerInRange($N);
            $h = $r->toHex();
            $n = hexdec($h);
            $c = $values[$n];
            $c2 = $c + 1;
            $values[$n] = $c2;
        }
        
        $this->assertEquals(0, $values[0]);
        $this->assertEquals(0, $values[255]);
        
        $sum = 0;
        for( $i = 0; $i < 256; ++$i) {
            $sum = $sum + $values[$i];
        }
        
        $this->assertEquals(1e3, $sum);
    }
    
    /**
     * Tests the PHP client session against test vectors taken from the JavaScript client. 
     * This test confirms that verifiers generated on the PHP client are interporable with the Java and JavaSCript client code so you can create users with PHP and login them in via a web browser. 
     */
    public function testPhpClientVerifier() {
        $N_base10str = "21766174458617435773191008891802753781907668374255538511144643224689886235383840957210909013086056401571399717235807266581649606472148410291413364152197364477180887395655483738115072677402235101762521901569820740293149529620419333266262073471054548368736039519702486226506248861060256971802984953561121442680157668000761429988222457090413873973970171927093992114751765168063614761119615476233422096442783117971236371647333871414335895773474667308967050807005509320424799678417036867928316761272274230314067548291133582479583061439577559347101961771406173684378522703483495337037655006751328447510550299250924469288819";
        $g_base10str = "2";
        $k_base16str = "5b9e8ef059c6b32ea59fc1d322d37f04aa30bae5aa9003b8321e21ddb04e300";
        $a_base16Str = "c87067749780c33412f903e9f93898146d4633ec16d94d63e1e4a909587513fe";
        $salt_base16Str = "046ffedc02d01f7b82a1f51312f3e9476023df82b96de300059b50dba286fcfe";
        $identify = "tom@arcot.com";
        $password = "password1234";
        
        $this->SrpClient = new NotRandomSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
        $this->SrpClient->setNotRandom($a_base16Str);
        
        $verifier = $this->SrpClient->generateVerifier($salt_base16Str, $identify, $password);
        
        $expected_v_base10Str = "10326106706451489320558143781108112346231733713840409481034924841710007022713731838617379744451831369708409511565425133108206727569433189678700354984169299968825650556996214337303634482407068637929750328493132928368917061689370557940252755499765005347881026583035229192814769133156298301869462999169935606060806840319948051654467131959235494625899534964649799292751557830967553419277880136463311151297491570381436737384670150213308502983187438539822428636037356011772744855647459621361699041221346145995728688117079378719053704166962154827774349157834812364827912733799407926553983799552682019384841119810112934665844";
        $expected_v_base16Str = (new Math_BigInteger($expected_v_base10Str, 10))->toHex();
        
        $this->assertEquals($expected_v_base16Str, $verifier);
    }
    
    public function testVerifierRejectsBlanks() {
        $success = true;
        
        try {
            $this->SrpClient->generateVerifier('', 'x', 'y');
            $success = false;
        } catch (\Exception $e) {
            // good
        }
        
        $this->assertTrue($success, 'blank salt was not detected');
        
        try {
            $this->SrpClient->generateVerifier('x', '', 'y');
            $success = false;
        } catch (\Exception $e) {
            // good
        }
        
        $this->assertTrue($success, 'blank identity was not detected');
        
        try {
            $this->SrpClient->generateVerifier('x', 'y', '');
            $success = false;
        } catch (\Exception $e) {
            // good
        }
        
        $this->assertTrue($success, 'blank password was not detected');
    }
    
    /**
     * Tests the PHP client session against test vectors taken from the JavaScript client.
     * This test mainly confirms that we can inject a random number such that the "a" and "A" will match for the latest steps. 
     */
    public function testPhpClientStep1() {
        $N_base10str = "21766174458617435773191008891802753781907668374255538511144643224689886235383840957210909013086056401571399717235807266581649606472148410291413364152197364477180887395655483738115072677402235101762521901569820740293149529620419333266262073471054548368736039519702486226506248861060256971802984953561121442680157668000761429988222457090413873973970171927093992114751765168063614761119615476233422096442783117971236371647333871414335895773474667308967050807005509320424799678417036867928316761272274230314067548291133582479583061439577559347101961771406173684378522703483495337037655006751328447510550299250924469288819";
        $g_base10str = "2";
        $k_base16str = "5b9e8ef059c6b32ea59fc1d322d37f04aa30bae5aa9003b8321e21ddb04e300";
        $a_base16Str = "c87067749780c33412f903e9f93898146d4633ec16d94d63e1e4a909587513fe";
        $salt_base16Str = "046ffedc02d01f7b82a1f51312f3e9476023df82b96de300059b50dba286fcfe";
        $identify = "tom@arcot.com";
        $password = "password1234";
        
        $this->SrpClient = new NotRandomSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
        $this->SrpClient->setNotRandom($a_base16Str);
        
        $Astr = $this->SrpClient->step1($identify, $password);
        
        $expected_A_base10Str = "17493445250770389037657809992660967910921898889902200001503851494369608684189333208491784683985404580440929611799091862118288574156227050949240015631263747928735936485984206443311126441695494535308677828143582668837274383495532199973884219755424962685333070047348334886192595953621872973006184010089496441885501414250114482860056465570044969803358441413321935602207027627964026986255909384927626463386994346747556356858825525450550981523048661460458660438348534094564674277055670359067769064668235224706789081562235339545503566682672342421458671475451055043916431165970094145584121922017912271902616247555180487850647";
        $expected_A_base16Str = (new Math_BigInteger($expected_A_base10Str, 10))->toHex();
        
        $this->assertEquals($expected_A_base16Str, $Astr);
        
        return;
    }
    
    /**
     * Tests the PHP client session against test vectors taken from the JavaScript client.
     */
    public function testPhpClientStep2() {
        $N_base10str = "21766174458617435773191008891802753781907668374255538511144643224689886235383840957210909013086056401571399717235807266581649606472148410291413364152197364477180887395655483738115072677402235101762521901569820740293149529620419333266262073471054548368736039519702486226506248861060256971802984953561121442680157668000761429988222457090413873973970171927093992114751765168063614761119615476233422096442783117971236371647333871414335895773474667308967050807005509320424799678417036867928316761272274230314067548291133582479583061439577559347101961771406173684378522703483495337037655006751328447510550299250924469288819";
        $g_base10str = "2";
        $k_base16str = "5b9e8ef059c6b32ea59fc1d322d37f04aa30bae5aa9003b8321e21ddb04e300";
        $a_base16Str = "c87067749780c33412f903e9f93898146d4633ec16d94d63e1e4a909587513fe";
        $salt_base16Str = "046ffedc02d01f7b82a1f51312f3e9476023df82b96de300059b50dba286fcfe";
        $identify = "tom@arcot.com";
        $password = "password1234";
        $B_base16Str = "1f302543c3fe1892cd9509ff7c10964712fb91097928aa4dcbd423da087143142c9805f540ea1d990634859d935fddc09a08a019bb3f59365c0b90f3452d98a37c34e99b79a500f79134d871e493bff0f7ad2a56ce5d356d4aa94d238eae7e960e367393d6592721263b0096a75012a83218a316b6b9280d078c9e3462ab2e68f0da1ee6144605c8c4d20297fe298523e33c496359d526d2179edc06d514fb991c50ee048498c4e2a484ad69c0a43cb5665584ecb44d57616d2afa2402d8723f548fb01ffa2f1971647f97475c0b2f7963d48176bda41e750e4389223d1a9c574312eb8cc839d3eb6f8e0ec110c26f3d3ec366cda55175113b9d10b11314a9ff";
    
        $this->SrpClient = new NotRandomSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
        $this->SrpClient->setNotRandom($a_base16Str);
    
        $this->SrpClient->step1($identify, $password);
    
        $credentials = $this->SrpClient->step2($salt_base16Str, $B_base16Str);
    
        $expected_A_base10Str = "17493445250770389037657809992660967910921898889902200001503851494369608684189333208491784683985404580440929611799091862118288574156227050949240015631263747928735936485984206443311126441695494535308677828143582668837274383495532199973884219755424962685333070047348334886192595953621872973006184010089496441885501414250114482860056465570044969803358441413321935602207027627964026986255909384927626463386994346747556356858825525450550981523048661460458660438348534094564674277055670359067769064668235224706789081562235339545503566682672342421458671475451055043916431165970094145584121922017912271902616247555180487850647";
        $expected_A_base16Str = (new Math_BigInteger($expected_A_base10Str, 10))->toHex();
        
        $actualA = $credentials[0];
        
        $this->assertEquals($expected_A_base16Str, $actualA);
        
        $expected_M1_base16Str = "a5a72a89de66233ef6cc8b31ad02f623985313f36ab70a2b76de7ce76822f08b";
        
        $actualM1 = $credentials[0];
        
        $this->assertEquals($expected_A_base16Str, $actualM1);
        
        return;
    }
    
    /**
     * Tests the PHP client session against test vectors taken from the JavaScript client.
     */
    public function testPhpClientSecretKey() {
        $N_base10str = "21766174458617435773191008891802753781907668374255538511144643224689886235383840957210909013086056401571399717235807266581649606472148410291413364152197364477180887395655483738115072677402235101762521901569820740293149529620419333266262073471054548368736039519702486226506248861060256971802984953561121442680157668000761429988222457090413873973970171927093992114751765168063614761119615476233422096442783117971236371647333871414335895773474667308967050807005509320424799678417036867928316761272274230314067548291133582479583061439577559347101961771406173684378522703483495337037655006751328447510550299250924469288819";
        $g_base10str = "2";
        $k_base16str = "5b9e8ef059c6b32ea59fc1d322d37f04aa30bae5aa9003b8321e21ddb04e300";
        $a_base16Str = "c87067749780c33412f903e9f93898146d4633ec16d94d63e1e4a909587513fe";
        $salt_base16Str = "046ffedc02d01f7b82a1f51312f3e9476023df82b96de300059b50dba286fcfe";
        $identify = "tom@arcot.com";
        $password = "password1234";
        $B_base16Str = "1f302543c3fe1892cd9509ff7c10964712fb91097928aa4dcbd423da087143142c9805f540ea1d990634859d935fddc09a08a019bb3f59365c0b90f3452d98a37c34e99b79a500f79134d871e493bff0f7ad2a56ce5d356d4aa94d238eae7e960e367393d6592721263b0096a75012a83218a316b6b9280d078c9e3462ab2e68f0da1ee6144605c8c4d20297fe298523e33c496359d526d2179edc06d514fb991c50ee048498c4e2a484ad69c0a43cb5665584ecb44d57616d2afa2402d8723f548fb01ffa2f1971647f97475c0b2f7963d48176bda41e750e4389223d1a9c574312eb8cc839d3eb6f8e0ec110c26f3d3ec366cda55175113b9d10b11314a9ff";
    
        $this->SrpClient = new NotRandomSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
        $this->SrpClient->setNotRandom($a_base16Str);
    
        $this->SrpClient->step1($identify, $password);
    
        $this->SrpClient->step2($salt_base16Str, $B_base16Str);
    
        $expected_S_base10Str = "16462450186225192713373638524945046049556649461476019228940267938409760471324667886988684273314563747738604537761035683075572709198079611906610309109387811075655406796864505307808358193612612746883376489753020446996186601397346102337051109399953141237583511631141635335334260716328572155692668967209125554153422896019957061880471630042263009976970780578432617886144848287813155393936368701977207143233530566164042227651856247358225737988835110354663300749078318855612423315917578646547392921122250893046134815655517611292264578271969351834839821604166289198647371425746900873247219032123796357988345172830229324002404";
        $expected_S_base16Str = (new Math_BigInteger($expected_S_base10Str, 10))->toHex();
        
        $actualS = $this->SrpClient->sessionKey(false);
    
        $this->assertEquals($expected_S_base16Str, $actualS);
        
        $actualShash = $this->SrpClient->sessionKey();
        
        $this->assertEquals($this->SrpClient->hash($expected_S_base16Str), $actualShash);
    
        return;
    }
    
    /**
     * Tests the PHP client session against the PHP server session. 
     */
    public function testMutualAuthentiation() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        // salt is created at user first registration
        $salt = $this->SrpClient->generateRandomSalt(); 
        $username = "tom@arcot.com";
        $password = "password1234";
        // verifier to be generated at the browser during user registration and password (or email address) reset only
        $v = $this->SrpClient->generateVerifier($salt, $username, $password);
        // normal login flow step1a client: browser starts with username and password given by user at the browser
        $this->SrpClient->step1($username, $password);
        // server challenge
        $B = $this->Srp->step1($username, $salt, $v);
        // client response is array of credentials
        $credentials = $this->SrpClient->step2($salt, $B);
        $A = $credentials[0];
        $M1 = $credentials[1];
        $M2 = $this->Srp->step2($A, $M1);
        $this->SrpClient->verifyConfirmation($M2);
        // noop assert else phpunit complains about this test. thinbus-php will have thrown exception if authenitication didn't work.
        $this->assertEquals(0, 0);

    }

    public function testMutualAuthentiationRepeatedly() {
        for( $j=0; $j<64; $j++ ) {
            $N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
            $g_base10str = "2";
            $k_base16str = "1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a";

            $this->Srp = new ThinbusSrp($N_base10str, $g_base10str, $k_base16str, "sha256");

            $this->SrpClient = new ThinbusSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");

            // salt is created at user first registration
            $salt = $this->SrpClient->generateRandomSalt();
            $username = "tom@arcot.com";
            $password = "password1234";
            // verifier to be generated at the browser during user registration and password (or email address) reset only
            $v = $this->SrpClient->generateVerifier($salt, $username, $password);
            // normal login flow step1a client: browser starts with username and password given by user at the browser
            $this->SrpClient->step1($username, $password);
            // server challenge
            $B = $this->Srp->step1($username, $salt, $v);
            // client response is array of credentials
            $credentials = $this->SrpClient->step2($salt, $B);
            $A = $credentials[0];
            $M1 = $credentials[1];
            $M2 = $this->Srp->step2($A, $M1);
            $this->assertTrue($this->SrpClient->verifyConfirmation($M2));
            // noop assert else phpunit complains about this test. thinbus-php will have thrown exception if authenitication didn't work.
            $this->assertEquals(0, 0);
            $this->tearDown();
        }

    }

    public function testWithJavaValues() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $B = $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $M2 = $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->assertEquals("d14d1a028b06ab00a14e1dd5518684d4d2811e452350b5f2d154efbf9e250755",$M2);
        $this->assertEquals("92d39597b7db73054a4b98fc3b7bda4aafa8ccda8b1b310178e6e62eda022c6f", $this->Srp->getSessionKey());
        $this->assertEquals("tom@arcot.com", $this->Srp->getUserID());
    }
    
    /**
     * @expectedException Exception
     */
    public function testOnlyGivesOneB() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
    }
    
    /**
     * @expectedException Exception
     */
    public function testOnlyValidatesOneM1() {
        $this->Srp->setNotRandom("823466d37e1945a2d4491690bdca79dadd2ee3196e4611342437b7a2452895b9564105872ff26f6e887578b0c55453539bd3d58d36ff15f47e06cf5de818cedf951f6a0912c6978c50af790b602b6218ebf6c7db2b4652e4fcbdab44b4a993ada2878d60d66529cc3e08df8d2332fc1eff483d14938e5a");
        $this->Srp->step1("tom@arcot.com", "2c7c4e8172a2b11af2278a6743a021acb8c497611b576a42d1bd1a2271732a40", "3e319ec41fbfb0d51cd99f01b2427fbe7ea5b4a5a3ec7b570b49a9ca2bb30b09abc395c462f002a619e66c315d9dff399bf82d35369c7567d443823e57de443476fbc4200c736297ad30ef968b80901d646d360499d470ba52b08f9d97885fac1ad8b1031bc44608903b87a6d2c31593f0e1151eaa137d");
        $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
        $this->Srp->step2("2e84e8d74359e1d446e23b5742c6eae1fc75e97e795371940c4e4d09edc89aa3eb0e957a88a4f1132a4620d2f85fad5577c8be08c35e0dec2600486705a6a81969f425a7a894209b9190e5afe5b2a19740bd8b739f2a741af9e370f07a6b63f91bd71cfa0a8b3c0f3d2eb5985d54837a7e5d5e19b2985b", "366c8c5219f263f5d6194727eec45e8f0eb3871046107d8101351d7a4ad5cd84");
    }
    
    public function testWithJavaValuesThinbus13() {
        // new Math_BigInteger("6f2f0345b7b927babd4342f7f28ba30fb8e739163c5e997cade873bc2ae16b57e582a379642f91e610dbb156132ee50540630ea8576c94a6d8d7813b39b5607637a98383a90bae88146e95fd09b559d447f41c65e4117d44c740b129c424e6afda46356417a78c051695843f2dc533c2d188f3b5d4ebee", 16)
        $this->Srp->setNotRandom("6f2f0345b7b927babd4342f7f28ba30fb8e739163c5e997cade873bc2ae16b57e582a379642f91e610dbb156132ee50540630ea8576c94a6d8d7813b39b5607637a98383a90bae88146e95fd09b559d447f41c65e4117d44c740b129c424e6afda46356417a78c051695843f2dc533c2d188f3b5d4ebee");
        // s c7ce7e7f7cd06cd296570c487886e4b18847a0a96b9d1571bb351c7cb3fd10c8
        // v 1c4adb908deffde2ccc738b2a9b773b61e2f6640df8459d8009a2f25e8c47ec73956552f3de9d912810955555afebd2e426b43df7d1ddd12f265a0f177fa03108e8939c4e0be6de5af18ebb486ea11f41da4ced644e0dc1d4a27c0aeb744b8205f509528a5edbeb17336670f8b76749868f6c4452c3ce1
        $B = $this->Srp->step1("tom@arcot.com", "c7ce7e7f7cd06cd296570c487886e4b18847a0a96b9d1571bb351c7cb3fd10c8", "1c4adb908deffde2ccc738b2a9b773b61e2f6640df8459d8009a2f25e8c47ec73956552f3de9d912810955555afebd2e426b43df7d1ddd12f265a0f177fa03108e8939c4e0be6de5af18ebb486ea11f41da4ced644e0dc1d4a27c0aeb744b8205f509528a5edbeb17336670f8b76749868f6c4452c3ce1");
        // B 7b9ce200f95227d16a03b43c73780c2adb469ff0b6b123e52507002c25ca32f2e097c6c66d4dd0d47c28eec7476e6945329fedfaf0d5a2411e334e69dbd6088e1fa8f92455e1786313547b266482d16a5755951fc396a0d6795e4ce80915dd3f06449e479726b8bde6ebd7f4d504175e1d616dfe22a16b
        // A 5fd6d73d866c4543250c9c04ab6964de0db8f3bad831dfa0e7edaf2f9862a60ea76313bf47ac475789a65f4459a9da4c2739957762084b9d5a2a7c76a33e1ef75ea6662c21d976fa9272b2b3019d7c14af5845de42000209f27127b3e29332c4eb944197c1ebcb4cbc1b543f97ed1e1b966c6a19d55f9f
        // M1 3f99d6b724102cacd2feb3f5ac5c54283bc954265d1d7919a8d59fd198019cb3
        $M2 = $this->Srp->step2("5fd6d73d866c4543250c9c04ab6964de0db8f3bad831dfa0e7edaf2f9862a60ea76313bf47ac475789a65f4459a9da4c2739957762084b9d5a2a7c76a33e1ef75ea6662c21d976fa9272b2b3019d7c14af5845de42000209f27127b3e29332c4eb944197c1ebcb4cbc1b543f97ed1e1b966c6a19d55f9f", "3f99d6b724102cacd2feb3f5ac5c54283bc954265d1d7919a8d59fd198019cb3");
        $this->assertEquals("4e9852f22ffe107c463b4037d3527992ee8d9b78318257ac3d2bbbb03c143946", $M2);
    }
    
    public function testSha1Vectors()
    {
        $projectDir = getenv('ZEND_PHPUNIT_PROJECT_LOCATION');
        
        $canary = 'x' . getenv('ZEND_PHPUNIT_PROJECT_LOCATION') . 'x';
        
        if ($canary == 'xx') {
            $projectDir = __DIR__;
        }
        
        // parse your data file however you want
        $data = array();
        foreach ( file($projectDir . '/test-vectors-sha1.txt') as $line) {
            $data[] = trim($line);
        }

        $username = $data[0];
        $password = $data[1];
        $g_base10 = $data[2];
        $N_base10 = $data[3];
        $k_base16 = $data[4];
        
        for ($i = 1; $i < 100; $i ++) {
            
            $s = $data[7*$i+0+5];
            $v = $data[7*$i+1+5];
            $b = $data[7*$i+2+5];
            $B = $data[7*$i+3+5];
            $A = $data[7*$i+4+5];
            $M = $data[7*$i+5+5];
            $M2 = $data[7*$i+6+5];
            
            $this->Srp = new NotRandomSrp($N_base10, $g_base10, $k_base16, "sha1");
            $this->Srp->setNotRandom($b);
            $Bs = $this->Srp->step1($username, $s, $v);
            $this->assertEquals($B, $Bs); // sanity check that the injected not random took hold
            $M2s = $this->Srp->step2($A, $M);
            $this->assertEquals($M2, $M2s);
            
        }
    }
}
?>
