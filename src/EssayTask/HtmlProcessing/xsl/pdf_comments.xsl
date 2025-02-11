<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
    <xsl:output method="xml" version="1.0" encoding="UTF-8"/>
    <xsl:param name="service_version" select="0"/>
    <xsl:param name="add_paragraph_numbers" select="0"/>
    
    <!--  Basic rule: copy everything not specified and process the children -->
    <xsl:template match="@*|node()">
        <xsl:copy><xsl:apply-templates select="@*|node()" /></xsl:copy>
    </xsl:template>

    <!-- elements to leave out -->
    <xsl:template match="html|body">
        <xsl:apply-templates select="node()" />
    </xsl:template>

    <!-- remove the number attributes -->
    <xsl:template match="@long-essay-number">
    </xsl:template>

    <!-- remove the table header -->
    <xsl:template match="thead">
    </xsl:template>

    <!-- add the comments column -->
    <xsl:template match="tr">
        <xsl:variable name="counter" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::initCurrentComments', string(@data-p))" />
        <xsl:copy>
            <xsl:copy-of select="@*" />
            <xsl:choose>
                <xsl:when test="$add_paragraph_numbers = 1">
                    <td style="width: 8mm;">
                        <!-- paragraph number -->
                        <xsl:apply-templates select="td[1]/node()" />
                    </td>
                    <td style="width: 92mm;">
                        <!-- text -->
                        <xsl:apply-templates select="td[2]/node()" />
                    </td>
                </xsl:when>
                <xsl:otherwise>
                    <td style="width: 100mm;">
                        <!-- text -->
                        <xsl:apply-templates select="td[1]/node()" />
                    </td>
                </xsl:otherwise>
            </xsl:choose>
             <td>
                <!-- comments -->
                <xsl:for-each select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::getCurrentComments')/node()">
                    <xsl:copy-of select="." />
                </xsl:for-each>
            </td>
        </xsl:copy>
    </xsl:template>
    
    <!-- add the marking and label for comments -->
    <xsl:template match="span">
        <xsl:choose>
            <xsl:when test="@data-w">
                <xsl:variable name="label" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::commentLabel',string(@data-w))" />
                <xsl:variable name="color" select="php:function('Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service::commentColor',string(@data-w))" />
                <xsl:choose>
                    <xsl:when test="$color">
                        <xsl:if test="$label">
                            <sup style="background-color: grey; color:white; padding:2px; font-family: sans-serif; font-size: 8px;">
                                <xsl:value-of select="$label" />
                            </sup>
                        </xsl:if>
                        <span>
                            <xsl:attribute name="style">background-color: <xsl:value-of select="$color" />;</xsl:attribute>
                            <xsl:value-of select="text()" />
                        </span>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:copy-of select="text()" />
                    </xsl:otherwise>
                </xsl:choose>
             </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="." />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
