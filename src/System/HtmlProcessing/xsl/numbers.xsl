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

    <!-- use specialized table elements for accessibility -->
    <xsl:template match="body">
        <xlas-table border="0" class="long-essay-content-table">
            <xsl:apply-templates select="node()" />
        </xlas-table>
    </xsl:template>

    <!--  Add numbers to the paragraph like elements -->
    <xsl:template match="body/h1|body/h2|body/h3|body/h4|body/h5|body/h6|body/p|body/ul|body/ol|body/pre|body/table">
        <xsl:variable name="counter" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::nextParaCounter')" />
        <xsl:variable name="prefix" select="php:function('Edutiek\AssessmentService\System\HtmlProcessing\Service::nextHeadlinePrefix', local-name())" />
        <xlas-tr class="long-essay-content-row">
            <xsl:attribute name="data-p">
                <xsl:value-of select="$counter" />
            </xsl:attribute>
            <xsl:if test="$add_paragraph_numbers = 1">
                <xlas-td class="long-essay-counter-column">
                    <p>
                        <xsl:attribute name="style">text-align:left;</xsl:attribute>
                        <span class="sr-only">Absatz</span>
                        <span class="long-essay-paragraph-number">
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
                        </span>
                    </p>
                </xlas-td>
            </xsl:if>
            <xlas-td class="long-essay-content-column">
                <xsl:copy>
                    <xsl:attribute name="class">long-essay-block</xsl:attribute>
                    <xsl:attribute name="long-essay-number">
                        <xsl:value-of select="$counter" />
                    </xsl:attribute>
                    
                    <xsl:choose>
                        <!-- from this version on headline prefixes should be included to the word counter for comment markup  -->
                        <xsl:when test="$service_version >= 20231218">
                            <xsl:call-template name="words">
                                <xsl:with-param name="text">
                                    <xsl:value-of select="$prefix" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$prefix" />
                        </xsl:otherwise>
                    </xsl:choose>

                    <xsl:apply-templates select="node()" />
                </xsl:copy>
            </xlas-td>
        </xlas-tr>
    </xsl:template>


    <!-- wrap words in word counter elements -->
    <xsl:template match="text()">
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
