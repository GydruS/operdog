<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="utf-8"/>

<xsl:variable name="USER_TYPE_GUEST" select="0"/>
<xsl:variable name="USER_TYPE_USER" select="1"/>
<xsl:variable name="USER_TYPE_ADMIN" select="7"/>
<xsl:variable name="userType" select="/document/context/user/type"/>
<xsl:variable name="coreRoot"><xsl:value-of select="/document/context/coreRoot"/><xsl:if test="/document/context/configRelativePath != ''">{<xsl:value-of select="/document/context/configRelativePath"/>}/</xsl:if><xsl:value-of select="/document/context/siteRootRelativePath"/></xsl:variable>
<xsl:variable name="siteRoot"><xsl:value-of select="/document/context/siteRoot"/><xsl:if test="/document/context/configRelativePath != ''">{<xsl:value-of select="/document/context/configRelativePath"/>}/</xsl:if></xsl:variable>
<xsl:variable name="siteRootWOCRP">/<xsl:value-of select="/document/context/siteRoot"/></xsl:variable><!-- WithOut Config Relative Path -->
<xsl:variable name="projectDir">/<xsl:if test="(/document/context/projectRelativeDir != '') and (/document/context/projectRelativeDir != '/')"><xsl:value-of select="/document/context/siteRoot"/><xsl:value-of select="/document/context/projectRelativeDir"/></xsl:if></xsl:variable>
<xsl:variable name="projectRelativeDir"><xsl:value-of select="/document/context/projectRelativeDir"/></xsl:variable>
<xsl:variable name="moduleName" select="/document/context/moduleName"/>
<xsl:variable name="modulesPath">/<xsl:value-of select="/document/context/siteRoot"/><xsl:value-of select="/document/context/modulesPath"/></xsl:variable>
<xsl:variable name="modulePath"><xsl:value-of select="$modulesPath"/><xsl:value-of select="/document/context/modulePath"/></xsl:variable>
<xsl:variable name="imgPath"><xsl:value-of select="$modulePath"/>img/</xsl:variable>
<xsl:variable name="commonImgPath"><xsl:value-of select="$modulesPath"/>common/img/</xsl:variable>
<xsl:variable name="builderModulePath">/<xsl:value-of select="/document/context/siteRoot"/><xsl:value-of select="/document/context/modulesPath"/>vpn_xslt_page_builder/</xsl:variable>
<xsl:variable name="iconsPath"><xsl:value-of select="$builderModulePath"/>img/icons/</xsl:variable>
<xsl:variable name="pageRelativeQuriedPath"><xsl:for-each select="/document/context/request/queriedStruct"><xsl:value-of select="."/>/</xsl:for-each></xsl:variable>
<xsl:variable name="MOBILE" select="/document/context/request/mobile"/>
<xsl:variable name="TABLET" select="/document/context/request/tablet"/>

<xsl:template name="errorsReporter">
    <xsl:if test="count(/document/context/errors/error) > 0">
        <div class="errors">
            <xsl:for-each select="/document/context/errors/error">
                <div class="alert alert-danger">
                    <xsl:value-of select="message"/>
                </div>
            </xsl:for-each>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template name="noticesReporter">
    <xsl:if test="count(/document/context/notices/notice) > 0">
        <div class="errors">
            <xsl:for-each select="/document/context/notices/notice">
                <div class="alert alert-success">
                    <xsl:value-of select="message"/>
                </div>
            </xsl:for-each>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template name="notices">
    <xsl:if test="count(/document/context/notices/notice) > 0">
        <div class="notices">
            <xsl:for-each select="/document/context/notices/notice">
                <div class="notice">
                    <xsl:if test="header != ''">
                        <b><xsl:value-of select="header"/></b>:
                    </xsl:if>
                    <xsl:value-of select="message"/>
                </div>
            </xsl:for-each>
        </div>
    </xsl:if>
</xsl:template>

<xsl:template name="readableData">
    <xsl:param name="node" select="/"/>
    <xsl:apply-templates mode="readableData" select="$node"/>
</xsl:template>

<xsl:template match="*" mode="readableData">
    <xsl:choose>
        <xsl:when test="count(./*)>0">
            <xsl:value-of select="name()"/> {
            <!--<div style="padding-left: {(count(ancestor::*)+1) * 20}px;">-->
            <div style="padding-left: 20px;">
                <xsl:for-each select="./*">
                    <xsl:apply-templates mode="readableData" select="."/>
                </xsl:for-each>
            </div>
            } <br/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="name()"/> = <xsl:value-of select="."/><br/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template name="tdEnabled">
    <xsl:param name="value"/>
    <td valign="top" nowrap="nowrap" align="center" width="70">
        <xsl:choose>
            <xsl:when test="$value = 1">
                <i class="fa fa-check fa-fw"></i>
            </xsl:when>
            <xsl:otherwise>
                <i class="fa fa-remove fa-fw"></i>
            </xsl:otherwise>
        </xsl:choose>
    </td>
</xsl:template>

<xsl:template name="navigation">
    <div class="col-sm-9 paginationAlign" align="left">
        <ul class="pagination" style="margin:0px; margin-bottom:20px;">
            <xsl:if test="/document/navigation/totalPages > 1">
                <xsl:call-template name="paginator"/>
            </xsl:if>
        </ul>
    </div>
    <div class="col-sm-3" align="right">
        <xsl:call-template name="showPerPage"/>
    </div>
</xsl:template>

<xsl:template name="navigationMobile">
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td valign="top" align="left" width="*" style="padding-right: 15px; padding-left: 15px;">
                <ul class="pagination" style="margin:0px; margin-bottom:20px;">
                    <xsl:if test="/document/navigation/totalPages > 1">
                        <xsl:call-template name="paginator"/>
                    </xsl:if>
                </ul>
            </td>
            <td valign="top" align="right" style="padding-right: 15px; padding-left: 15px;">
                <xsl:call-template name="showPerPageMobile"/>
            </td>
        </tr>
    </table>
</xsl:template>

<xsl:template name="paginator">
    <xsl:param name="min" select="1"/>
    <xsl:param name="max" select="/document/navigation/totalPages"/>
    <xsl:param name="index" select="/document/navigation/page"/>
    <xsl:param name="viewCountAtIndex" select="3"/>
    <xsl:param name="viewCountAtBound" select="3"/>
    <xsl:param name="current" select="$min"/>
    <xsl:param name="noSpacer" select="0"/>
    <xsl:choose>
        <xsl:when test="$current &lt; $max">
            <xsl:if test="$current = $min">
                <xsl:if test="$index > $min"><li><a class="prev" href="?page={$index - 1}">←</a></li></xsl:if>
            </xsl:if>
            <xsl:choose>
                <xsl:when test="($current = $min) or ($current = $max) 
                                or 
                                (($current &gt; $index - $viewCountAtIndex) and ($current &lt; $index + $viewCountAtIndex))
                                or
                                (($index &lt; $min + $viewCountAtBound) and (($current &lt; $min + $viewCountAtBound + $viewCountAtIndex)))
                                or
                                (($index &gt; $max - $viewCountAtBound) and (($current &gt; $max - $viewCountAtBound - $viewCountAtIndex)))
                ">
                    <xsl:call-template name="paginatorVal"><xsl:with-param name="current" select="$current"/><xsl:with-param name="index" select="$index"/></xsl:call-template>
                    <xsl:call-template name="paginator">
                        <xsl:with-param name="current" select="$current + 1"/>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:if test="$noSpacer != 1"><li><a href="#" class="disabled">...</a></li></xsl:if>
                    <xsl:call-template name="paginator">
                        <xsl:with-param name="current" select="$current + 1"/>
                        <xsl:with-param name="noSpacer" select="1"/>
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="$current = $max">
            <xsl:call-template name="paginatorVal"><xsl:with-param name="current" select="$current"/><xsl:with-param name="index" select="$index"/></xsl:call-template>
            <xsl:if test="$index &lt; $max"><li><a class="next" href="?page={$index + 1}">→</a></li></xsl:if>
        </xsl:when>
    </xsl:choose>
</xsl:template>

<xsl:template name="paginatorVal">
    <xsl:param name="index" select="1"/>
    <xsl:param name="current" select="1"/>
    <li>
        <xsl:attribute name="class">
            <xsl:if test="$current = $index">active</xsl:if>
        </xsl:attribute>
        <a href="?page={$current}">
            <xsl:value-of select="$current"/>
        </a>
    </li>
</xsl:template>

<xsl:template name="showPerPageMobile">
    <form role="form" class="form-inline">
        <div class="form-group itemsPerPageGroup">
            <label style="float: left; padding-top: 6px;">Показывать по:</label>
            <select id="itemsPerPage" class="form-control">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="0">All</option>
            </select>
        </div>
    </form>
</xsl:template>

<xsl:template name="showPerPage">
    <form role="form" class="form-inline">
        <div class="form-group itemsPerPageGroup">
            <label>Записей на странице:&#160;</label>
            <select id="itemsPerPage" class="form-control">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="0">All</option>
            </select>
        </div>
    </form>
</xsl:template>

<xsl:template name="filters">
    <xsl:if test="count(/document/filters/filter[1]/*) > 0">
    <div class="panel panel-default filtersPanel">
        <div class="panel-heading">
            <!--<div class="pull-left">Filter</div>
            <div class="pull-right">
                <div class="panelBodySlide">
                    <i class="fa fa-minus-square-o tooltip-info" data-toggle="tooltip" title="Show/hide filter" data-placement="left"></i>
                </div>
            </div>
            <br/>-->
            Filter
        </div>
        <div class="panel-body">
            <form role="form" class="table-form filters-form">
                <xsl:for-each select="/document/filters/filter">
                    <label class="control-label">
                        <xsl:if test="./label != ''">
                            <xsl:value-of select="./label"/>:
                        </xsl:if>
                    </label>
                    <div class="form-group">
                        <xsl:choose>
                            <xsl:when test="./type = 'select'">
                                <select class="filters form-control" id="{./name}">
                                    <option value="all">All</option>
                                    <xsl:for-each select="./values">
                                        <option value="{id}"><xsl:value-of select="name"/></option>
                                    </xsl:for-each>
                                </select>
                            </xsl:when>
                            <xsl:when test="./type = 'datetime'">
                                <input type="text" class="inputDatepicker filters form-control" id="{./name}" value=""/>
                            </xsl:when>
                            <xsl:otherwise>
                                <input type="text" class="filters form-control" id="{./name}" value=""/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </div>
                </xsl:for-each>
                <div class="form-group">
                    <xsl:if test="/document/filterMode = 'onSubmit'">
                        <button id="submitFilters" class="btn btn-primary">Apply</button>
                    </xsl:if>
                </div>
            </form>
        </div>
    </div>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>
