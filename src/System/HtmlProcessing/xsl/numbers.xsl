<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    <xsl:param name="add_paragraph_numbers" select="0"/>
    
    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- don't copy the html element -->
    <xsl:template match="html">
        <xsl:variable name="counter" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::initParaCounter')" />
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!--  Add paragraph numbers to all direct children of the body -->
    <xsl:template match="body/*">
        <xsl:variable name="counter" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::nextParaCounter')" />
        <xsl:variable name="prefix" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::nextHeadlinePrefix', local-name())" />
        <xsl:text>&#10;</xsl:text>
        <!-- add a visible paragraph counter -->
        <xsl:if test="$add_paragraph_numbers = 1">
            <div class="xlas-counter">
                <xsl:attribute name="data-p">
                    <xsl:value-of select="$counter" />
                </xsl:attribute>
                <span class="sr-only">Absatz</span>
                <xsl:choose>
                    <xsl:when test="$service_version >= 20231218">
                        <!-- from this version on paragraph numbers should be included to the word counter for comment markup  -->
                        <xsl:call-template name="words">
                            <xsl:with-param name="text">
                                <xsl:value-of select="$counter" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$counter" />
                    </xsl:otherwise>
                </xsl:choose>
            </div>
        </xsl:if>

        <!-- copy the element and add the counter attibute -->
        <xsl:copy>
            <xsl:copy-of select="@*"></xsl:copy-of>
            <xsl:attribute name="data-p">
                <xsl:value-of select="$counter" />
            </xsl:attribute>

            <!-- add a selectable prefix to the headline, according the headline scheme -->
            <xsl:if test="$prefix">
                <xsl:call-template name="words">
                    <xsl:with-param name="text">
                        <xsl:value-of select="$prefix" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:if>

            <xsl:apply-templates select="node()" />
        </xsl:copy>

    </xsl:template>

    <xsl:template match="span">
        <xsl:copy><xsl:copy-of select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- wrap words in word counter elements, use only test that has real content -->
    <xsl:template match="text()[normalize-space()]">
        <xsl:call-template name="words">
            <xsl:with-param name="text">
                <xsl:value-of select="string(.)" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="words">
        <xsl:param name="text" />
        <xsl:variable name="para" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::currentParaCounter')" />
        <xsl:for-each select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::splitWords', $text)/text()">
            <xsl:variable name="word" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::nextWordCounter')" />
            <w-p>
                <xsl:attribute name="w"><xsl:value-of select="$word" /></xsl:attribute>
                <xsl:attribute name="p"><xsl:value-of select="$para" /></xsl:attribute>
                <xsl:value-of select="." />
            </w-p>
        </xsl:for-each>
    </xsl:template>

</xsl:stylesheet>
