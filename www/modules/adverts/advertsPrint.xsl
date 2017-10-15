<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8"/>

<xsl:template match="/document">
    <xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <HTML style="height:100%">
        <HEAD>
            <TITLE><xsl:value-of select="/document/info/siteName"/></TITLE>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
            <xsl:call-template name="documentCSS"/>
            <xsl:call-template name="documentJS"/>
        </HEAD>
        <BODY onload="onLoad()">
            <div align="center">
                <div class="container">
                    <xsl:call-template name="advert"/>
                </div>
            </div>
        </BODY>
    </HTML>
</xsl:template>

<xsl:template name="advert">
<!--    <span class="advertAddedDatetime">
        <!- -<span class="infoLabel">От</span>&#160;- ->
        <span class="value">
            <xsl:value-of select="/document/item/addedDate"/>
        </span>
    </span>-->
    <!--<h1>Помогите найти друга!</h1>-->
    <xsl:call-template name="images"/>
    <h1><xsl:call-template name="advertName"/></h1>
    <!--<h2><xsl:value-of select="/document/item/name"/></h2>-->
    <!--<br/>-->
    <div class="advertText"><xsl:value-of select="/document/item/text"/></div>
    
    <xsl:variable name="place"><xsl:value-of select="/document/item/city"/><xsl:if test="(/document/item/city != '') and (/document/item/lastSeenPlace != '')">, </xsl:if><xsl:value-of select="/document/item/lastSeenPlace"/></xsl:variable>
    <xsl:if test="$place != ''">
        <div class="advertLastSeenPlace">
            <span class="infoLabel">Ориентировочное место пропажи:</span>&#160;<span class="value"><xsl:value-of select="$place"/></span>
        </div>
    </xsl:if>
    <div class="infoBlock">
        <span class="infoLabel">Доп. информация: </span>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Окрас'"/><xsl:with-param name="value" select="/document/item/color"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Глаза'"/><xsl:with-param name="value" select="/document/item/eyes"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Уши'"/><xsl:with-param name="value" select="/document/item/ears"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Хвост'"/><xsl:with-param name="value" select="/document/item/tail"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Порода'"/><xsl:with-param name="value" select="/document/item/breed"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Возраст'"/><xsl:with-param name="value" select="/document/item/age"/></xsl:call-template>
        <xsl:variable name="gender"><xsl:choose><xsl:when test="/document/item/gender = 'Male'">Мужской</xsl:when><xsl:when test="/document/item/gender = 'Female'">Женский</xsl:when><xsl:otherwise></xsl:otherwise></xsl:choose></xsl:variable>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Пол'"/><xsl:with-param name="value" select="$gender"/></xsl:call-template>
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Особые приметы'"/><xsl:with-param name="value" select="/document/item/specialSigns"/></xsl:call-template>
        <!--<xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Обстоятельства пропажи'"/><xsl:with-param name="value" select="/document/item/text"/></xsl:call-template>-->
        <xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Дата пропажи'"/><xsl:with-param name="value" select="/document/item/eventDate"/><xsl:with-param name="noComma" select="'1'"/></xsl:call-template>
        <!--<xsl:call-template name="advertCardParam"><xsl:with-param name="name" select="'Вознаграждение'"/><xsl:with-param name="value" select="/document/item/reward"/></xsl:call-template>-->
    </div>
    
    
    
    <div class="advertPhone">
        <span class="infoLabel">Телефон для связи:</span>&#160;
        <span class="value">
            <xsl:value-of select="/document/item/phone"/>
            <xsl:if test="/document/item/contacterName != ''">, <xsl:value-of select="/document/item/contacterName"/></xsl:if>
        </span>
    </div>
<!--    <div class="advertPhone"><span class="infoLabel">Телефон:</span>&#160;<span class="value"><xsl:value-of select="/document/item/phone"/></span></div>
    <xsl:if test="/document/item/contacterName != ''">
        <div class="advertContacterName"><span class="infoLabel">Контактное лицо:</span>&#160;<span class="value"><xsl:value-of select="/document/item/contacterName"/></span></div>
    </xsl:if>-->
    <xsl:if test="/document/item/reward != ''">
        <span class="advertReward"><span class="infoLabel">Вознагражение:</span>&#160;<span class="value"><xsl:value-of select="/document/item/reward"/></span></span>
    </xsl:if>
    <div class="source">
        <span class="infoLabel"></span>&#160;
        <span class="value">
            <script language="javascript">
                document.write(window.location.hostname+advertLink);
            </script>
        </span>
    </div>
    
    <xsl:call-template name="cutOffs"/>
</xsl:template>

<xsl:template name="advertCardParam">
    <xsl:param name="name"/>
    <xsl:param name="value"/>
    <xsl:param name="class" seect="''"/>
    <xsl:param name="noComma" seect="'0'"/>
    <xsl:if test="$value != ''">
        <span class="advertParam {$class}">
            <span class="name"><xsl:value-of select="$name"/>: </span><span class="value"><xsl:value-of select="$value" disable-output-escaping="yes"/></span>
        </span>
        <xsl:if test="$noComma != '1'">,&#160;</xsl:if>
    </xsl:if>
</xsl:template>

<xsl:template name="cutOffs">
    <div class="cutOffs">
        <xsl:call-template name="cutOff"/>
        <xsl:call-template name="cutOff"/>
        <xsl:call-template name="cutOff"/>
        <xsl:call-template name="cutOff"/>
    </div>
</xsl:template>

<xsl:template name="cutOff">
    <div class="cutOff">
        <div class="qrcode"></div>
        <xsl:call-template name="advertName"/><br/>
        <div class="phone"><xsl:value-of select="/document/item/phone"/></div>
    </div>
</xsl:template>

<xsl:template name="advertName">
    <xsl:variable name="id" select="/document/item/id"/>
    <xsl:variable name="categoryId" select="/document/item/categoryId"/>
    <xsl:variable name="name">
        <xsl:choose>
            <xsl:when test="/document/item/name != ''"><xsl:value-of select="/document/item/name"/></xsl:when>
            <xsl:when test="(/document/item/name = '') and (/document/item/categoryId = 0)"></xsl:when>
            <xsl:otherwise><!--Пропал(а): -->
                <xsl:variable name="category" select="/document/categories/category[id=$categoryId]"/>
                <xsl:choose>
                    <xsl:when test="(/document/item/gender = 'Male') and ($category/nameMale != '')"><xsl:value-of select="$category/nameMale"/></xsl:when>
                    <xsl:when test="(/document/item/gender = 'Female') and ($category/nameFemale != '')"><xsl:value-of select="$category/nameFemale"/></xsl:when>
                    <xsl:otherwise><xsl:value-of select="$category/name"/></xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="/document/item/nickname != ''">&#160;<xsl:value-of select="/document/item/nickname"/></xsl:if>
    </xsl:variable>
    <xsl:variable name="caption"><xsl:choose><xsl:when test="/document/item/gender = 'Male'">Потерялся</xsl:when><xsl:when test="/document/item/gender = 'Female'">Потерялась</xsl:when><xsl:otherwise>Потерялось животное -</xsl:otherwise></xsl:choose></xsl:variable>
    <xsl:value-of select="$caption"/>&#160;<xsl:value-of select="$name"/>
</xsl:template>

<xsl:template name="images">
    <xsl:if test="count(/document/item/images/image/id)>0">
        <!--<h2>Фотографии</h2>-->
        <div class="row">
            <div class="col-sm-12">
                <xsl:for-each select="/document/item/images/image">
                    <!--<xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/>lib/ge_imager.php?pic=<xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="path"/></xsl:variable>-->
                    <xsl:variable name="currentImagePath"><xsl:value-of select="/document/context/siteRoot"/><xsl:value-of select="/document/context/configRelativeDir"/><xsl:value-of select="path"/></xsl:variable>
                    <div class="imagePreview" align="center">
                        <!--<a href="/{$currentImagePath}" data-lightbox="blogItem">-->
                            <!--<img class="blogPreviewImage" src="/{$currentImagePath}&amp;h=160&amp;w=160&amp;p=1" alt="{description}" title="{description}"/>-->
                            <img class="" src="/{$currentImagePath}" alt="{description}" title="{description}"/>
                        <!--</a>-->
                    </div>
                </xsl:for-each>
            </div>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template name="documentCSS">
<style>
body, html {
    padding: 0px;
    margin: 0px;
    height: 100%;
    font-family:Arial, Helvetica, sans-serif; font-size:12px;
}
img {
    max-height: 100mm;
    max-width: 100%;
    display: block;
}

.cutOffs {
    margin-top: 10mm;
    border-top: dashed 1px black;
}
.cutOffs  :last-child {
    border-right: none;
}
.cutOff {
    padding: 2mm;
    border-right: 1px solid black;
    display: inline-block;
    width: 40mm;
}
.container {
    padding: 1mm;
    width: 180mm;
    height: 250mm;
}
.infoLabel {color:#666; font-weight: bold;}
.advertLastSeenPlace, .advertPhone, .advertContacterName, .advertReward {
    display: block;
    padding-top: 12px;
}
.advertPhone, .advertReward, .advertLastSeenPlace {
    font-size: 18px;
}
.advertText {
    font-size: 16px;
}
.source {
    padding-top: 24px;
    color: #666;
}
.qrcode {
    padding-top: 10mm;
    padding-bottom: 5mm;
}
.phone {
    padding-top: 2mm;
}
.infoBlock {
    padding-top: 10px;
}
</style>
</xsl:template>

<xsl:template name="documentJS">
    <xsl:for-each select="/document/scripts/script">
        <script language="javascript" src="{.}"></script>
    </xsl:for-each>
    
    <script language="javascript">        
        var siteRoot = '/<xsl:value-of select="$siteRoot"/>';
        var advertId = '<xsl:value-of select="/document/item/id"/>';
        var advertLink = siteRoot + 'adverts/view/' + advertId;
        function printpage() {
            <xsl:if test="count(/document/disableAutoPrint) = 0 or /document/disableAutoPrint = '' or /document/disableAutoPrint = 0">
                window.print();
            </xsl:if>
            <xsl:if test="count(/document/disableAutoClose) = 0 or /document/disableAutoClose = '' or /document/disableAutoClose = 0">
                setTimeout(function() {
                    window.close();
                }, 1000);
            </xsl:if>
        }
    </script>
</xsl:template>

</xsl:stylesheet>