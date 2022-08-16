<?php



use OTPHP\TOTP;

/**
 * Funktionen für die Zweifaktorauthentifizierung
 * @author Christian
 *
 */

class WebAuthnPage extends AbstractPage {

    public function __construct() {

        parent::__construct ( array (
            "2 - Faktor"
        ) );

        if(!self::is2FAActive()) {
            header("Location: index.php");
            exit(0);
        }

    }

    public function execute() {
        // Register Webauthn

        // Deregister Webauthn
    }

    public static function hasSettings() {
        return true;
    }

    public static function getSettingsDescription() {
        return [
            [
                'name' => "webauthn-trenner-1",
                'typ' => 'TRENNER',
                'titel' => "Allgemeine Einstellungen",
                'text' => ""
            ],
            [
                'name' => "webauthn-active",
                'typ' => 'BOOLEAN',
                'titel' => "Webauthn aktiv",
                'text' => "Webauthn aktivieren? Ab dann können Benutzer auch"
            ],
            [
                'name' => "2fa-allow-trusted-devices",
                'typ' => 'BOOLEAN',
                'titel' => "Vertrauenswürdige Geräte aktivieren",
                'text' => "Bei aktivieter Option kann bei der Eingabe eines Codes die Option \"Code auf diesem Gerät nicht mehr anfordern\" aktiviert werden."
            ],
            [
                'name' => "2fa-trenner-2",
                'typ' => 'TRENNER',
                'titel' => "Spezielle Bereiche",
                'text' => ""
            ],
            [
                'name' => "2fa-active-admin",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für die Administration erzwingen?",
                'text' => ""
            ],
            [
                'name' => "2fa-active-noten",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für die Notenverwaltung erzwingen?",
                'text' => ""
            ],
            [
                'name' => "2fa-trenner-3",
                'typ' => 'TRENNER',
                'titel' => "Generell",
                'text' => ""
            ],
            [
                'name' => "2fa-active-schueler",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für Schüler erzwingen?",
                'text' => ""
            ],
            [
                'name' => "2fa-active-lehrer",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für Lehrer erzwingen?",
                'text' => ""
            ],
            [
                'name' => "2fa-active-eltern",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für Eltern erzwingen?",
                'text' => ""
            ],
            [
                'name' => "2fa-active-sonstige",
                'typ' => 'BOOLEAN',
                'titel' => "2FA für Sonstige Benutzer erzwingen?",
                'text' => ""
            ],
        ];
    }


    public static function getSiteDisplayName() {
        return 'Zweifaktor';
    }

    /**
     * Liest alle Nutzergruppen aus, die diese Seite verwendet. (Für die Benutzeradministration)
     * @return array(array('groupName' => '', 'beschreibung' => ''))
     */
    public static function getUserGroups() {
        return array();
    }

    public static function hasAdmin() {
        return true;
    }

    public static function getAdminMenuGroup() {
        return "Benutzerverwaltung";
    }

    public static function getAdminMenuGroupIcon() {
        return 'fa fa-users';
    }

    public static function getAdminMenuIcon() {
        return 'fa fa-key';
    }

    public static function dependsPage() {
        return [];
    }

    public static function userHasAccess($user) {

        return true;
    }

    public static function siteIsAlwaysActive() {
        return true;
    }

    public static function displayAdministration($selfURL) {
        switch($_REQUEST['action']) {
            default:
                return self::showIndex($selfURL);
                break;

            case 'Remove2FA':
                return self::remove2FA($selfURL);
                break;
        }
    }

    private static function remove2FA($selfURL) {
        $user = user::getUserByID($_REQUEST['userID']);

        if($user != null) {
            if($user->has2FA()) {
                $user->set2FA("");
            }
        }

        header("Location: $selfURL");
        exit(0);

    }

    private static function showIndex($selfURL) {
        $users = user::getAll();

        $usersWith2FA = [];

        for($i = 0; $i < sizeof($users); $i++) {
            if($users[$i]->has2FA()){
                $usersWith2FA[] = $users[$i];
            }
        }



        $tableHTML = "";

        for($i = 0; $i < sizeof($usersWith2FA); $i++) {
            $tableHTML .= "<tr><td>" . $usersWith2FA[$i]->getUserName() . "</td>";
            $tableHTML .= "<td><a href=\"" . $selfURL . "&action=Remove2FA&userID=" . $usersWith2FA[$i]->getUserID() . "\" class=\"btn btn-danger btn-sm\"><i class=\"fa fa-trash\"></i> 2FA entfernen</a></td></tr>";
        }


        $html = "";

        eval("\$html .= \"" . DB::getTPL()->get("userprofile/2fa/admin/index") . "\";");
        return $html;



    }



    // Allgemeine Warapper für Einstellungen

    /**
     * Ist 2FA für einen bestimmten Nutzer generell aktiv?
     * @param unknown $user
     */
    public static function TwoFactorForUser(user $user) {
        return $user->is2FAActive();
    }

    /**
     * Ist 2 Fatktor generell aktiv?
     * @return boolean
     */
    public static function is2FAActive() {
        return DB::getSettings()->getBoolean('2fa-active');
    }

    /**
     * Erlaubten ein Gerät für den Nutzer zu einem Gerät ohne erneute Nachfrage zu machen.
     * @return boolean
     */
    public static function allowTrustedDevices() {
        return DB::getSettings()->getBoolean('2fa-allow-trusted-devices');
    }

    public static function force2FAForAdmin() {
        return DB::getSettings()->getBoolean('2fa-active-admin');
    }

    public static function force2FAForNoten() {
        return DB::getSettings()->getBoolean('2fa-active-noten');
    }

    public static function enforcedForUser(user $user) {
        if($user->isPupil() && DB::getSettings()->getBoolean('2fa-active-schueler')) {
            return true;
        }

        else if($user->isTeacher() && DB::getSettings()->getBoolean('2fa-active-lehrer')) {
            return true;
        }

        else if($user->isEltern() && DB::getSettings()->getBoolean('2fa-active-eltern')) {
            return true;
        }

        else if(DB::getSettings()->getBoolean('2fa-active-sonstige')) {
            return true;
        }
        else return false;
    }

}

?>