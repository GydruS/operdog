<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:import href="../common/common.xsl"/>
<xsl:output method="html" encoding="utf-8" indent="yes"
      doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
      doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
/>
<xsl:variable name="imgPath"><xsl:value-of select="$modulesPath"/>xslt_page_builder/img/</xsl:variable>

<xsl:template match="/document">
    <xsl:call-template name="document"/>
</xsl:template>

<xsl:template name="document">
    <HTML style="height:100%">
        <HEAD>
            <TITLE><xsl:call-template name="documentTitle"/></TITLE>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
            <xsl:call-template name="keywords"/>
            <xsl:call-template name="description"/>
            <xsl:call-template name="documentCSS"/>
            <xsl:call-template name="documentJS"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <meta property="og:image" content="{/document/info/siteURL}{$modulePath}img/operdog-socialnetworks.png"/>
            <meta property="og:type" content="website"/>
            <meta property="og:url" content="{/document/info/siteURL}"/>
            <meta property="og:title" content="Опердог – платформа поиска пропавших животных во Владивостоке"/>
            <meta property="og:description" content="Доска объявлений о пропавших животных"/>
            <link rel="icon" href="/{/document/context/siteRoot}{/document/context/siteRootRelativePath}favicon.ico" type="image/x-icon"/>            
            <xsl:value-of select="/document/counters" disable-output-escaping="yes"/>
        </HEAD>
        <body>
            <xsl:attribute name="class">
                <xsl:value-of select="queriedModule"/>
                <xsl:if test="$MOBILE = '1'"> mobile</xsl:if>
            </xsl:attribute>
            <script>
                var MOBILE = parseInt('<xsl:value-of select="$MOBILE"/>');
            </script>
            <xsl:call-template name="topMenu"/>
            <div class="wrapper">
                <div class="container">
                    <xsl:if test="(count(/document/context/errors/error) > 0) or (count(/document/context/notices/notice) > 0)">
                        <div class="row systermMessagesBlock">
                            <div class="col-xs-12">
                                <xsl:call-template name="errorsReporter"/>
                                <xsl:call-template name="noticesReporter"/>
                            </div>
                        </div>
                    </xsl:if>
                    <xsl:call-template name="content"/>
                </div>
            </div>
            <xsl:call-template name="footer"/>
        </body>
    </HTML>
</xsl:template>

<xsl:template name="content">
    <xsl:value-of select="queriedModuleContent" disable-output-escaping="yes"/>
</xsl:template>

<xsl:template name="footer">
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-sm-3 text-left">
                </div>
                <div class="col-sm-6 text-center">
                    <span class="creds">
                        Проект создан при поддержке <a href="https://te-st.ru" target="_blank">Теплицы социальных технологий</a>
                    </span>
                </div>
                <div class="col-sm-3 text-right">
                    <!--<p>© GydruS 2017</p>-->
                    <a href="http://gydrus.com">
                        <!--<img id="GSLogo" src="{$projectDir}modules/common/img/GSLogo14x14.png" width="14" height="14" style="position:relative; top:-1px;"/>-->
                        <span class="gs" aria-hidden="true">G</span>
                    </a>
                    <!--<p>Powered by GeThree</p>-->
                </div>
            </div>
        </div>
    </footer>
</xsl:template>

<xsl:template name="topMenu">
    <div class="navbar navbar-white navbar-static-top" role="navigation">
        <div class="container">
            <!-- Navbar Header -->
            <div class="navbar-header" align="left">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <!--<strong class="caret"></strong>-->
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/{$siteRoot}">
                    <!--<xsl:value-of select="/document/info/siteName"/>-->
                    <img id="Logo" src="{$imgPath}logo.svg" style=""/>
                </a>
            </div> <!-- / Navbar Header -->

            <!-- Navbar Links -->
            <div class="navbar-collapse collapse" align="center"> <!-- navbar-right -->
                <ul class="nav navbar-nav text-center">
                    <xsl:call-template name="topMenuItem">
                        <xsl:with-param name="caption" select="'Все объявления'"/>
                        <xsl:with-param name="link" select="'adverts'"/>
                        <xsl:with-param name="action" select="'list'"/>
                        <!--<xsl:with-param name="iconClass" select="'glyphicon-th-list'"/>-->
                    </xsl:call-template>
                    <xsl:call-template name="topMenuItem">
                        <xsl:with-param name="caption" select="'О проекте'"/>
                        <xsl:with-param name="link" select="'about'"/>
                    </xsl:call-template>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li class="nohover noactive">
                        <!--<xsl:attribute name="class">
                            <xsl:if test="(/document/queriedModule = 'adverts') and (/document/queriedModuleAction = 'new')">active</xsl:if>
                        </xsl:attribute>-->
                        <a class="navButton" href="/{$siteRoot}adverts/new">
                            <button class="btn btn-danger">
                                Разместить объявление
                            </button>
                        </a>
                    </li>                    
                    <!--<xsl:call-template name="topMenuItem">
                        <xsl:with-param name="caption" select="'Разместить объявление'"/>
                        <xsl:with-param name="moduleName" select="'adverts'"/>
                        <xsl:with-param name="action" select="'new'"/>
                        <xsl:with-param name="link" select="'adverts/new'"/>
                        <!- -<xsl:with-param name="iconClass" select="'glyphicon-plus-sign'"/>- ->
                    </xsl:call-template>-->
                    <xsl:call-template name="topMenuItem">
                        <xsl:with-param name="caption" select="'Мои объявления'"/>
                        <xsl:with-param name="moduleName" select="'adverts'"/>
                        <xsl:with-param name="action" select="'myAdverts'"/>
                        <xsl:with-param name="link" select="'adverts/myAdverts'"/>
                        <!--<xsl:with-param name="iconClass" select="'glyphicon-heart-empty'"/>-->
                    </xsl:call-template>
                    <xsl:if test="$userType != $USER_TYPE_GUEST">
                        <!--<xsl:value-of select="/document/context/user/login"/>-->
                        <li>
                            <a href="/{$siteRoot}?logout=1" data-toggle="tooltip" data-placement="bottom" data-original-title="Выйти">
                                <span class="glyphicon glyphicon-log-out"/>
                            </a>
                        </li>
                    </xsl:if>
                </ul>
            </div> <!-- / Navbar Links -->
        </div> <!-- / container -->
    </div> <!-- / navbar -->
</xsl:template>

<xsl:template name="topMenuItem">
    <xsl:param name="caption"/>
    <xsl:param name="link"/>
    <xsl:param name="iconClass" select="''"/>
    <xsl:param name="moduleName" select="$link"/>
    <xsl:param name="action" select="''"/>
    <li>
        <xsl:attribute name="class">
            <xsl:if test="(/document/queriedModule = $moduleName) and (($action='') or (/document/queriedModuleAction = $action))">active</xsl:if>
        </xsl:attribute>
        <a href="/{$siteRoot}{$link}">
            <!--<xsl:if test="$iconClass != ''"><i class="fa-li fa {$iconClass}"></i>&#160;</xsl:if>-->
            <xsl:if test="$iconClass != ''"><span class="glyphicon {$iconClass}"/>&#160;</xsl:if>
            <xsl:value-of select="$caption"/>
        </a>
    </li>
</xsl:template>

<!--###############################################-->
<xsl:template name="documentTitle">
	<xsl:call-template name="documentTitleMain"/>
</xsl:template>

<xsl:template name="documentTitleMain">
	<!--<xsl:value-of select="/document/info/siteName"/>-->
    Operdog.ru – поиск пропавших животных
</xsl:template>

<xsl:template name="documentCSS">
    <xsl:text >&#xa;</xsl:text>
    <xsl:for-each select="/document/styles/style">
        <link href="{.}" rel="stylesheet" type="text/css"/><xsl:text >&#xa;</xsl:text>
    </xsl:for-each>
</xsl:template>

<xsl:template name="documentJS">
    <xsl:text >&#xa;</xsl:text>
    <script language="javascript">
        var userType = <xsl:value-of select="/document/context/user/type"/>;
        var userId = <xsl:choose><xsl:when test="/document/context/user/type > 0"><xsl:value-of select="/document/context/user/id"/></xsl:when><xsl:otherwise>0</xsl:otherwise></xsl:choose>;
        var ROOT_DIR = '<xsl:value-of select="$siteRoot"/>';
        var siteRoot = '/'+ROOT_DIR;
        var MODULES_PATH = '<xsl:value-of select="$modulesPath"/>';
    </script>
    <xsl:text >&#xa;</xsl:text>
    <xsl:for-each select="/document/scripts/script">
        <script language="javascript" type="application/javascript" src="{.}"></script><xsl:text >&#xa;</xsl:text>
    </xsl:for-each>
</xsl:template>

<xsl:template name="keywords">
    <meta name="keywords">
        <xsl:attribute name="content">
            <xsl:for-each select="/document/path/item">
                <xsl:if test="keywords != ''">
                    <xsl:if test="position()&gt;1 and position()&lt;last()">, </xsl:if>
                    <xsl:value-of select="keywords"/>
                </xsl:if>
            </xsl:for-each>
        </xsl:attribute>
    </meta>
</xsl:template>

<xsl:template name="description">
    <meta name="description">
        <xsl:attribute name="content">
            <xsl:for-each select="/document/path/item">
                <xsl:if test="description != ''">
                    <xsl:if test="position()&gt;1 and position()&lt;last()">, </xsl:if>
                    <xsl:value-of select="description"/>
                </xsl:if>
            </xsl:for-each>
        </xsl:attribute>
    </meta>
</xsl:template>

</xsl:stylesheet>