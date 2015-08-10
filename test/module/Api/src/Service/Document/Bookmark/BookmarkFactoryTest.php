<?php
namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BookmarkFactory;

/**
 * Bookmark Factory test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BookmarkFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider allBookmarksProvider
     */
    public function testGetClassNameFromToken($token, $expected)
    {
        if (empty($expected)) {
            $this->markTestSkipped('Skipping '. $token);
        }

        $sut = new BookmarkFactory();

        $className = $sut->getClassNameFromToken($token);

        $this->assertEquals($expected, $className);
    }

    /**
     * Includes all bookmarks used in olcs-templates, mapped to existing classes
     * where they exist in the Dvsa\OlcsTest\Api\Service\Document\Bookmark
     * namespace.
     */
    public function allBookmarksProvider()
    {
        return [
            ['AandD_stored_publication_date', ''],
            ['AandD_stored_publication_number', ''],
            ['ADDITIONAL_UNDERTAKINGS', ''],
            ['Address_1', ''],
            ['ADDRESS_OF_ESTABLISHMENT', 'AddressOfEstablishment'],
            ['applicant_name', 'ApplicantName'],
            ['application_type', ''],
            ['application_type_NI', ''],
            ['AUTHORISED_VEHICLES', 'AuthorisedVehicles'],
            ['AuthorisedDecision', ''],
            ['AuthorisorName2', ''],
            ['AuthorisorName3', ''],
            ['AuthorisorTeam', ''],
            ['background_to_imposition_of_conditions', ''],
            ['background_to_imposition_of_conditions_n', ''],
            ['BR_COUNCILS_NOTIFIED', 'BrCouncilsNotified'],
            ['BR_DATE_RECEIVED', 'BrDateReceived'],
            ['BR_EFFECTIVE_DATE', 'BrEffectiveDate'],
            ['BR_LOGO', 'BrLogo'],
            ['BR_LOGO', 'BrLogo'],
            ['BR_N_P_NO', 'BrNPNo'],
            ['BR_OP_ADDRESS', 'BrOpAddress'],
            ['BR_REASON_FOR_VAR', 'BrReasonForVar'],
            ['BR_REG_NO_OUR_REF', 'BrRegNoOurRef'],
            ['BR_REG_NOBR_FINISH_POINT', ''],
            ['BR_ROUTE_NUM', 'BrRouteNum'],
            ['BR_ROUTE_NUMBR_EFFECTIVE_DATE', ''],
            ['BR_START_POINT', 'BrStartPoint'],
            ['caseworker_details', 'CaseworkerDetails'],
            ['caseworker_name', 'CaseworkerName'],
            ['caseworker_name1', 'CaseworkerName1'],
            ['Company_Trading_Name', 'CompanyTradingName'],
            ['CONDITIONS', 'Conditions'],
            ['Conditions', ''],
            ['Cont_Next_Exp_Date', 'ContNextExpDate'],
            ['CONTINUATION_DATE', 'ContinuationDate'],
            ['Continuation_Date1', 'ContinuationDate1'],
            ['Continuation_Date2', 'ContinuationDate2'],
            ['Continuation_Date3', 'ContinuationDate3'],
            ['Continuation_Date4', 'ContinuationDate4'],
            ['Continuation_Date6', 'ContinuationDate6'],
            ['COPY_TYPE', ''],
            ['Date_From', 'DateFrom'],
            ['Date_To', 'DateTo'],
            ['dear_sir_or_madam', ''],
            ['Disc_List', 'DiscList'],
            ['DISCS_ISSUED', 'DiscsIssued'],
            ['DOC_CATEGORY_ID_SCAN', ''],
            ['DOC_CATEGORY_NAME_SCAN', ''],
            ['DOC_DESCRIPTION_ID_SCAN', ''],
            ['DOC_DESCRIPTION_NAME_SCAN', ''],
            ['DOC_SUBCATEGORY_ID_SCAN', ''],
            ['DOC_SUBCATEGORY_NAME_SCAN', ''],
            ['ENTITY_ID_REPEAT_SCAN', ''],
            ['ENTITY_ID_SCAN', ''],
            ['ENTITY_ID_TYPE_SCAN', ''],
            ['ERRATA_SECTION', ''],
            ['European_Licence_Number', 'EuropeanLicenceNumber'],
            ['FEE_DUE_DATE', 'FeeDueDate'],
            ['FEE_REQ_GRANT_NUMBER', 'FeeReqGrantNumber'],
            ['FOOTER_LICENCE_NUMBER', 'FooterLicenceNumber'],
            ['FStanding_AdditionalVeh', 'FStandingAdditionalVeh'],
            ['FStanding_CapitalReserves', 'FStandingCapitalReserves'],
            ['FStanding_FirstVeh', 'FStandingFirstVeh'],
            ['FStanding_ProvedDate', 'FStandingProvedDate'],
            ['GV_LIC_FEE2', 'GvLicFee2'],
            ['ins_more_freq_no', 'InsMoreFreqNo'],
            ['ins_more_freq_yes', 'InsMoreFreqYes'],
            ['ins_no_trailers', 'InsNoTrailers'],
            ['ins_no_vhls', 'InsNoVhls'],
            ['INT_LIC_FEE', 'IntLicFee'],
            ['INTERIM_LICENCE_TYPE', 'InterimLicenceType'],
            ['INTERIM_OPERATING_CENTRES', 'InterimOperatingCentres'],
            ['INTERIM_SPECIFIC_LICENCE_CONDITIONS', 'InterimSpecificLicenceConditions'],
            ['INTERIM_SPECIFIC_LICENCE_UNDERTAKINGS', 'InterimSpecificLicenceUndertakings'],
            ['INTERIM_STANDARD_CONDITIONS', 'InterimStandardConditions'],
            ['INTERIM_TRAILERS', 'InterimTrailers'],
            ['INTERIM_UNLINKED_TM', 'InterimUnlinkedTm'],
            ['INTERIM_VALID_DATE', 'InterimValidDate'],
            ['INTERIM_VEHICLES', 'InterimVehicles'],
            ['ISSUE_DATE', 'IssueDate'],
            ['letter_date_add_10_days', 'LetterDateAdd10Days'],
            ['letter_date_add_14_days', 'LetterDateAdd14Days'],
            ['letter_date_add_21_days', 'LetterDateAdd21Days'],
            ['letter_date_add_28_days', 'LetterDateAdd28Days'],
            ['Lic_Address', 'LicAddress'],
            ['Lic_Mail_Address', 'LicMailAddress'],
            ['Lic_Mail_Name', 'LicMailName'],
            ['Licence_Holder_Address', 'LicenceHolderAddress'],
            ['licence_holder_address', 'LicenceHolderAddress'],
            ['licence_holder_name', 'LicenceHolderName'],
            ['LICENCE_NUMBER', 'LicenceNumber'],
            ['Licence_Number', 'LicenceNumber'],
            ['licence_number', 'LicenceNumber'],
            ['Licence_Number1', 'LicenceNumber1'],
            ['Licence_Number2', ''],
            ['Licence_Number3', 'LicenceNumber3'],
            ['Licence_Number4', 'LicenceNumber4'],
            ['Licence_Number5', 'LicenceNumber5'],
            ['Licence_Number6', 'LicenceNumber6'],
            ['Licence_Number7', 'LicenceNumber7'],
            ['Licence_Number8', 'LicenceNumber8'],
            ['licence_number__01', 'LicenceNumber01'],
            ['LICENCE_NUMBER_REPEAT', 'LicenceNumberRepeat'],
            ['licence_number_repeat', 'LicenceNumberRepeat'],
            ['LICENCE_NUMBER_REPEAT_SCAN', ''],
            ['LICENCE_NUMBER_SCAN', ''],
            ['Licence_Operating_Centres', 'LicenceOperatingCentres'],
            ['Licence_Partners', 'LicencePartners'],
            ['licence_review_date', 'LicenceReviewDate'],
            ['LICENCE_TITLE', 'LicenceTitle'],
            ['Licence_Trailer_Limit', 'LicenceTrailerLimit'],
            ['LICENCE_TYPE', 'LicenceType'],
            ['Licence_Type', 'LicenceType'],
            ['Licence_Vehicle_Limit', 'LicenceVehicleLimit'],
            ['Name', ''],
            ['NandP_stored_publication_date', ''],
            ['NandP_stored_publication_number', ''],
            ['NO_DISCS_PRINTED', 'NoDiscsPrinted'],
            ['OBJ_DEADLINE', 'ObjDeadline'],
            ['OP_ADDRESS', 'OpAddress'],
            ['op_address', 'OpAddress'],
            ['OP_DETAILS', 'OpDetails'],
            ['OP_FAO_NAME', 'OpFaoName'],
            ['op_fao_name', 'OpFaoName'],
            ['op_name', 'OpName'],
            ['OP_Name_Only', 'OpNameOnly'],
            ['OPERATING_CENTRES', 'OperatingCentres'],
            ['OPERATOR_NAME', 'OperatorName'],
            ['Operator_Name', 'OperatorName'],
            ['Original_Copy', 'OriginalCopy'],
            ['p_GV_OR_PSV', ''],
            ['p_GV_OR_PSV_NI', ''],
            ['p_PI_S35_GV_PSV_S54', ''],
            ['p_PI_S35_GV_PSV_S54_NI', ''],
            ['p_unacceptable_advert', ''],
            ['p_unacceptable_advert_NI', ''],
            ['Phone_Numbers', 'PhoneNumbers'],
            ['PI_HEARING_DATE', 'PiHearingDate'],
            ['PI_HEARING_VENUE', 'PiHearingVenue'],
            ['POLICE_PEOPLE', ''],
            ['POLICE_PERSON', ''],
            ['Psv_Disc_Page', 'PsvDiscPage'],
            ['PSV_STANDARD_CONDITIONS', 'PsvStandardConditions'],
            ['PUBLICATION_DATE', 'PublicationDate'],
            ['PUBLICATION_NUMBER', 'PublicationNumber'],
            ['reason_for_closure', ''],
            ['reason_for_closure_NI', ''],
            ['reason_for_no_review', ''],
            ['reason_for_no_review_NI', ''],
            ['Registered_Number', 'RegisteredNumber'],
            ['RequestDate', ''],
            ['RequestMode', ''],
            ['REVIEW_DATE', 'ReviewDate'],
            ['Review_Date_Add_2_Months', 'ReviewDateAdd2Months'],
            ['S43_AUTHORISED_DECISION', 'S43AuthorisedDecision'],
            ['S43_REQUEST_MODE', 'S43RequestMode'],
            ['S43_Requestor_Name_Body_Address', 'S43RequestorNameBodyAddress'],
            ['S9_authorised_decision', 'S9AuthorisedDecision'],
            ['S9_authorisors_age', 'S9AuthorisorsAge'],
            ['S9_REQUEST_MODE', 'S9RequestMode'],
            ['S9_Requestor_Name_Body_Address', 'S9RequestorNameBodyAddress'],
            ['safety_insp_morefreq', ''],
            ['SafetyAddresses', 'SafetyAddresses'],
            ['SECTION1_1', 'Section11'],
            ['SECTION1_2', 'Section12'],
            ['SECTION2_1', 'Section21'],
            ['SECTION2_10', ''],
            ['SECTION2_2', 'Section22'],
            ['SECTION2_3', 'Section23'],
            ['SECTION2_4', 'Section24'],
            ['SECTION2_5', 'Section25'],
            ['SECTION2_6', 'Section26'],
            ['SECTION2_7', 'Section27'],
            ['SECTION2_9', 'Section29'],
            ['SECTION3_1', 'Section31'],
            ['SECTION3_2', 'Section32'],
            ['SECTION3_3', 'Section33'],
            ['SECTION3_4', 'Section34'],
            ['SECTION3_5', 'Section35'],
            ['SECTION3_6', 'Section36'],
            ['SECTION4_1', 'Section41'],
            ['SECTION4_2', 'Section42'],
            ['SECTION5_1', 'Section51'],
            ['SECTION5_2', 'Section52'],
            ['SECTION5_3', 'Section53'],
            ['SECTION5_4', 'Section54'],
            ['SECTION6_1', 'Section61'],
            ['SECTION7_1', 'Section71'],
            ['SECTION7_2', 'Section72'],
            ['SECTION8_1', 'Section81'],
            ['SERIAL_NO_PREFIX', 'SerialNoPrefix'],
            ['SERIAL_NUM', 'SerialNum'],
            ['SPECIFIC_LICENCE_CONDITIONS', 'SpecificLicenceConditions'],
            ['SPECIFIC_LICENCE_UNDERTAKINGS', 'SpecificLicenceUndertakings'],
            ['subject_address', 'SubjectAddress'],
            ['subject_operating_centre_address', 'SubjectOperatingCentreAddress'],
            ['TA_ADD1', ''],
            ['TA_ADDRESS', 'TaAddress'],
            ['TA_ADDRESS_PHONE', 'TaAddressPhone'],
            ['TA_NAME', 'TaName'],
            ['TA_Name', 'TaName'],
            ['TA_NAME_UPPERCASE', 'TaNameUppercase'],
            ['TAAddress_1', ''],
            ['TAAddress_2', ''],
            ['tachograph_details', 'TachographDetails'],
            ['TAName', 'TaName'],
            ['TC_SIGNATURE', 'TcSignature'],
            ['TM_ADDRESS', 'TmAddress'],
            ['tm_id', 'TmId'],
            ['TM_NAME', 'TmName'],
            ['today_date_sentence', 'TodayDateSentence'],
            ['TODAYS_DATE', 'TodaysDate'],
            ['todays_date', 'TodaysDate'],
            ['TotalContFee', 'TotalContFee'],
            ['TRADING_NAME', ''],
            ['Trading_Names', 'TradingNames'],
            ['TRAILERS', 'Trailers'],
            ['Transport_Managers', 'TransportManagers'],
            ['Two_Weeks_Before', 'TwoWeeksBefore'],
            ['UNDERTAKINGS', 'Undertakings'],
            ['UNLINKED_TM', 'UnlinkedTm'],
            ['UserKnownAs', ''],
            ['VALID_DATE', 'ValidDate'],
            ['VEHICLE_ROW', 'VehicleRow'],
            ['VEHICLES', 'Vehicles'],
            ['Vehicles_Specified', 'VehiclesSpecified'],
            ['warning_re_early_operating', ''],
            ['warning_re_early_operating_NI', ''],
        ];
    }
}
