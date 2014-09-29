<?php
/**
 * Youstice form for reporting claims.
 *
 * @author    Youstice
 * @copyright (c) 2014, Youstice
 * @license   http://www.apache.org/licenses/LICENSE-2.0.html  Apache License, Version 2.0
 */

class YousticeWidgetsReportClaimsForm {

	protected $action;
	protected $translator;

	public function __construct($lang)
	{
	}

	public function toString()
	{
		$order_number_text = $this->translator->t('Order number');
		$description_text = 'Would you like to file a complaint and report on your shopping issue? Simply enter the details below:';

		$output = '<h2>'.$this->translator->t('File a complaint').'</h2>';
		$output .= '<img style="float:left; margin-right:15px;" src="'.$this->getImageData().'"/>';
		$output .= '<p style="clear:left;max-width:300px;padding-top:8px">'.$this->translator->t($description_text).'</p>';
		$output .= '<form action="" method="post" id="yReportClaims">';
		$output .= '<label for="yEmail">Email</label>';
		$output .= '<input type="email" id="yEmail" name="email">';
		$output .= '<label for="yOrderNumber">'.$order_number_text.'</label>';
		$output .= '<input type="text" id="yOrderNumber" name="orderNumber">';
		$output .= '<input type="submit" name="send" value="'.$this->translator->t('Continue').'">';
		$output .= '</form>';

		return $output;
	}

	protected function getImageData()
	{
		$image_data  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQ0AAABACAMAAAA3fdI8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccl';
		$image_data .= 'lPAAAAxRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+';
		$image_data .= 'IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxM';
		$image_data .= 'i8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YX';
		$image_data .= 'gtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHh';
		$image_data .= 'tbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9i';
		$image_data .= 'ZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDEwOUQ5REZENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcE1NOkluc';
		$image_data .= '3RhbmNlSUQ9InhtcC5paWQ6RDEwOUQ5REVENDQ4MTFFM0I4RTI5QTlBOTUzQzk2NzQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIE';
		$image_data .= 'NTNS4xIE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSIyREI0RkE1NTBCMkE0OTYzRTI5NTEyQkRCNjUwQTU';
		$image_data .= 'wMSIgc3RSZWY6ZG9jdW1lbnRJRD0iMkRCNEZBNTUwQjJBNDk2M0UyOTUxMkJEQjY1MEE1MDEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJE';
		$image_data .= 'Rj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7w3Bx0AAADAFBMVEXl5eWAFn7Oo83t4e2lYaT4+PmuZa2rXaqxaLH27faTk5S0crO9j';
		$image_data .= 'LzFkMS9hLx9EnvSrNGampuwZ7B2DHSta6yeXJ3ExMWGIYScVJuLHIircaquYa3MnMu0g7OzfLKzs7Ty6PK7u7zDjMKvY656enzp2emKGYf19fWpYa';
		$image_data .= 'iqqqulXaXNqszJycqMIol8JHqvaq5uDmzo1ujx5vHU1NXQp9ClUKOKiotlAWSdQJrEmsOjo6SnYaepZKj06vTt7e3y8vKDg4Tl0eWfV578+/yJIob';
		$image_data .= '8+fzd3d7+/v6EHoLiyOGUMZLAib+NJIl+Gny9kbz69vqSS5CCMYHhxeCSKo+TLpDewd3Vr9SDFYCjTaHev92OK4zVutWLPYqoXqekaqPp6emdTJt4';
		$image_data .= 'FnaROI/t3e3m1eWxd7D27/baudp2FXSkX6KhXaDgyN+sZKrZwdmNMouZPpeSJ4/exd6ya7CgRp51GnPZ2dnfyd7SsdKAKX/48/j48viOIIt5GHmNQ';
		$image_data .= 'otpBmiQIY3s2uysYKuaOpiQJY3FoMW5fLjYutji0OKCGYBqCGlxEXCmWqWVUJPo0+jhzOC2bLS3ebXWtNWjWKHHk8b+/P6nVKb59vloaGq0aLLZtd';
		$image_data .= 'hwEm6OJItoBGaBHICRJo5uD22KIYfTtdKYNZXIpMdxDHB4EHblzeSzb7KPj5CJFobq3OluCW2ZVZdyFHGTOpFsCmvkyuOSQJDs2OtiYmTz6fOoaae';
		$image_data .= 'iZKG5driGF4N/Hn7ZvtiIHoXXsteHGYSRJI7Il8fcwNzdvdx0FXOPRo59F3uILYakW6JtDGyGhod+foD69Pr59PmJO4eenqB0EHLbw9vOr82pV6ia';
		$image_data .= 'Qpfv4++GN4R4H3e5g7jUt9PIm8iQJo21arOwYK5gAF9rDWqurq/X19f38PeXl5jb29zv7/C3t7inp6j9/f3Pz8/69vm4ibe2dLXg4ODn0ueXSpazb';
		$image_data .= 'LN6GXqIJ4br2+u/v8BgYGJxcXP6+vqINYeCEH/59vrcxdv07PT7+Pt8HXvz6/Lk0uP///9A738jAAAT0ElEQVR42uyaCVgT57rHWRN2BEQIYRGjAc';
		$image_data .= 'KOQlQELUGCsihKhaKouAKHKuJGDQYRtQRcEJGrEAgFoqHAwXoFQQqotUbaYkUqdUUpFqu0Qj3V3mrt3Pebycp2bJ+jR/v4b4mT2TLzy7v8v2+igr2';
		$image_data .= 'VXCpvEbyl8VdoPP32kvtbGhJNqDE393N8SwNXrrV5XFy838dvaeB5ohEfHxdnvv8tDaQva/zi4uPNv34zbuTZrHmgd9tfWt0oNY+P97P44M2goW2c';
		$image_data .= 'RqVS2xa8NBq5O/z8LDzekCDXPqoFon768jrsR+Z+Fs9ex1tnbTuO9KUSDeOXS2OmhV+8+anXkcadh1QajUb+8VXGxiZUN6zZryMNXTwQlGgYk19u3';
		$image_data .= 'bhvbgo4JryeNIwH0Hh2ZjXo8PKXRaPd1NQ03tR8ypsRGy/bb5RWIhqmGqteQxpHXzWNvBo/nIb5R68fjS+OkhAN91dH430IDcTDbyVrhOO/HHtCpr';
		$image_data .= 'F4N9aWrxkra4HaRxZc1ddvnrROZl/YPid8CJ2Q+LtcH6lOzFHsbMdXqzUfaFZbPYFYy4Ljlh81BtHm4XuzsB75kV8oXd2JWaMsDzRbLpjlMbAXjJ2';
		$image_data .= 'HtqitXt7zYjSsgEZNqDkExx8j0NicM817mkRx69CaDu+EGvQuoSaBMYrY6Q/90YysLAYDXhIMTs8kQu8ByZWQ/STJBSYZa7m6JiW5aiW8Kzv/lgXZ';
		$image_data .= 'NDI5jZpGJlNPTvJBI2vjpKQkLURDK+khSGsbpv0AXwLRDssvjX1s3ENoMzTqjRs3Dhkc3qJw1cfGXb9BiGYw70VoTNhtGl+54zlU0srQEWiMMo+Hu';
		$image_data .= '2TExcUx4vxwGtZZDO9oXN45OI12vegse4QHaVp0VvYRnMZR+4Q0fFWWGnEqDxI1gQb/kUhZ70lPv+cBmWYsFY18dBaGuaeRSCQoG2gNCf6oxzHth9';
		$image_data .= 'KdyHK/sTyIStMidOjQIdqNk8dkcTEO8Bw6dP369X37rt9o2+nx72lYiK8kP3HHrMBz1D4dgYYfgIjTYMRpaGjk4DR2MrylwmmsS8BZSHEkJEQzJkH';
		$image_data .= 'c5unaJ5AS4NZJ0TIaUpGlseFOSiMZK4hG3YO5UxXXaGnRjkt8KFKajMa7WlQtuXAekihw170BGPZdP0Rr81y/ffZ8yuR2JRqnTHeH5g0Yzl8RC7hb';
		$image_data .= 'e7BvzyY/ebJjIIN/TJnyIbHkuNTvPOLB0DifY47fxEq/LIIFIyvSEcN2MRj2OA17CBYJj6zmHizPFaIHvZHTSCOnpRE0JLExJzsNjwBIEzKBJS0Ic';
		$image_data .= '0cuCwdhTIIlKhloaFElcSCjsZoqAWRMoxkTPGj7fPCY2Uc7dKMtcnJ4E4dOT20qSkykRH6hQMORy+TfrFQaHm8J5ednckthKaw6syLMI0+p2Diagx';
		$image_data .= 'bii6esrVaex6NjZYf1O2jNFKuOGgQj2qLD+hS2l+Ftj8RguFrv1PXOmiZNjmehHXoGrig8pDSeBhkEGSjRmEdGSUEijVtwZn9zEsoG2oPcdoOgIAM';
		$image_data .= 'iMDqCkNSxLfrwz0NFGkcIGMZU45MGBieNqVoPH+7bd+MMbPE5WFISnlhET9RSO6xGS21KBJUHyWnMCeMLBG78HR++v7k01MpiceXcr8JcqgSZ4mRU';
		$image_data .= 'ePT4VVWZLmFffVZZa2Glt+PSrn+s2GRuGqcRXyMtS2M1UNlQmDJ8WhOn4e19HrmBp6NxGt5ZQZtW9bDzJlydhsdDAgOvHaxse4gNad1A8jFOUKBhS';
		$image_data .= 'cbhzCLqgC4N0cBjeDkeKJA2cs15gNJGQkNbF4VKEk1LbVteLnvO8klpbW2R68P1YdPJ8qb0okSKJ9GdP52PcDSFt8totFeJBYBDfIvHZyLx+VxetZ';
		$image_data .= 'sgk2mN18ANAsAhKBNXVyCJy6piU/pcksOWLVohvZDQHA2NuPPyRrzLD4rI+excWLyaA1FiH+29S7rNXRfHEZ2NujHrgX2CPDbwluiqSGMcSpw0A0z';
		$image_data .= 'OhkrQ2EbQUHRfXzwwlseGGhVvMCdlPl2N09TUhELAPT09MbGIEym1lJMoKDjoB2Q08pK5iIab2E0glZtYLOYJiBFKLVOQmVlVVXUWFEvoIvqLUZd+';
		$image_data .= '1N6fzHPiNc7LvFDHeQ1vDb/NqBRIKoi8Y2Ieo+1RtmSdRlZiZTRkCkOBxlicRrSEhj6iQd0p8QpqCUlJxtn/NwyNDx5oyTrsCWrbwcj12yeryz91f';
		$image_data .= 'mp6Oh1oGNDh5ouajsjmcNanwgqOJ1tWN3ageODxCBAoPvjcW/kbNiyW3KuLy4Yqt2oel8fNJICUJZeVlfGt5a51WWy9WbKZdEW7hgaqmicIN2IPre';
		$image_data .= 'WqYtGZxUDtJdoApzEwNiQ0JPQm4ZmSJnEtW8B2PX+KX/Y22qBR2wf7yOSDnpPxc12lpxekppZbKoy5ZkO1pI/DZk5GN0+hybcEITxNsz3kPeXS7q3';
		$image_data .= '3P0pmAgtx/uL7oTsuvb/3Y49VUvv2w3P1FZsWOk7Rs/oKcMRmVqz0uL9oa6iCuwut6EtJESZLok8v5PM1y9YgNmxrc0a0t/c0H6WG1IFyxd7VR0qD';
		$image_data .= 'MSyNn3FrQUob92Ouck/bhvwGjfyO4oh+e0F6U0H5OLRMphQVFaVu/1Jxegh03AcSpQjRsHw2Ryo1lCpFqccG+I32uUyoH7zK4QdppVcyq85WbCgdP';
		$image_data .= 'Nqtj+nr6zMhvkPWJ8Jz586ZoETx+bzerL/frEZ57/1+0ajJQLA+zp5GciVFT1Kookm4O5Vm1tU21GBJZHK22hFFt/5jScns2eGc9xRplHDS04voqF';
		$image_data .= 'Bu2Z4KNCi6Q9zCp3iZSNw+WabtCE8i5fBA98Wy4FYLBEyXYWa75izmQfnghm0aYlutSUpK38V4wovEnAsJMXyCLt49BMnkvvLOswL7+/ujAiEi5ix';
		$image_data .= 'ds2xZyZogBa6X14JmS+dsviQ5w12Hh0cVFBevNZCHwjGKs3NBAeXMQBrpOI3l85uKihLpzUNc6FU6TqMpVSa8xSZSFgz2oqX50Gu5t4Z04h9v5WdW';
		$image_data .= 'ZfIrnw+18fTFFFA93qdWmiACHTiYcyGFhYVCtQFhHlhYGBhoqIcKjmpgYKFhtnybewPSr7Ij7jxojGgoLkb33lDnTJK21GPFBQXD09hTkA6xQR81x';
		$image_data .= 'IXqD6JBqPzTIZz53jAoHtXMxXmDTrPQBWCIq61yh561/QThKEMTQ3lrYvpC+kLcJTRCCusH00AiaGSYmZkZ7pRvezejuNjfX1XhiHnRxRENzs7BcP';
		$image_data .= 'fOdcGWL0SjCacxaXgaay9PVtb2Y0ONU55XQvFwY24d+LgxVMzLz+dtGPah7JQKoHHxJ4RNGBLSJzTH165AiVJoojfA1qviNK7iNAKVaRxQ9S8uLo5';
		$image_data .= 'Q4vejwdrGugacR3HdVTmN4GEzJbEJ0dAf4jotcRoF8/IGij30qM2KC62WeeVbxQmB57VMcb6A+c3wQ3uP+tiUlJgUiIiasj5IFALb034IjhBhjvK+';
		$image_data .= 'zUKcxi6CBiwp0MiBYBlIA+rLad3ZEcXBwKPYeQ9BwxnFxntD07hTgldR2hDXuRqvopRJLzzbc2kD4OBfUWwtVjfBmHFNR5oSrKkAGGX3Me3k2L6+m';
		$image_data .= 'M/vEDMMPwlRdASqK+7JXmoICAr91WU0Vsq2vdNghjSQBmo2zeF4eNThRWYWHhv01UPTwNoo0F6awscOvszl89MRDeqLz339sUEs4H+mGBsfQXHlW4';
		$image_data .= 'w4h7buYkxMysVF2KUy6C5l0vvTQxU1RKh06GYEI1B1KfrO/4loqDJk26JVcRqSumGZcDTJ1UB6Ie7hUEyDKetR5TqC143G5mFo6Jeno2WDwZfZ48l';
		$image_data .= 'B9qLg2AvT8AAaTCulabMr1QKm9Yg02J8AjhjhilqgkRK7VzplFIJSJcRQod78/Pk56CiBeGllX4YS0lC4RmqTmgkYUho0k4yIX9fKjsyuQzT+iXZW';
		$image_data .= 'v4bipGF9rhKNggIJjW3XCoBGk7Ni7GB7krRlLZazXlthyw9nRqDhyBS4cd9XWjWXL+B+1TMijikVQKOs40ksqqayXUeXIRrnzsm8+bo1htB0C1WX4';
		$image_data .= 'QTOE3FCGI4e/cJAMzxVVIm8Torw92+YLcvPo0DDuWEtGu7NvNwQDGWkcWeuMo2CcqJ06pYXAQ7ONXl98Bk3/1fUkJ6WpOKVw1P+4OVMJOf48DQWMw';
		$image_data .= 'XVLkTity+UPGdigmcf+WctT10gOGKf/A4JUyYfl7Qnx+A4hOabPVg9q9ZZ1OMwCoXEr0I6cBqBhSvXffyH2lLVQLNABKNBFffXmGsEdNsMe0nV2R8';
		$image_data .= 'MmeLcmIW/2VkXjNToqfbOz8QQ406Jc3p6QTkxGG0PR2mTzqGQD4/twdja8wxK6E2p29EchGV5EW7OZwftAYM4Z5vaQQ6H8nBYGqwwnoBPzP3suMI3';
		$image_data .= 'xRl8zXMTMEtHnn23QMERi/5cFMYlm4UpuCEVCvsXffL5OWFIPYJhIjFcpwkagYYZZoGGACPj8k/IgQT25+TYa2NBhtBu/TOiEvRL1YJyGhqCgUYE4';
		$image_data .= 'Tj2FDvjOCh1zg2uACfy4MEoCI10ztq2Ns89yIGnXoPwaKJQZkcejCzh0FGXoaNvKZdK4GiiJE6GUd58CqcIRvizhqOxwg3KBrKjH85l8tyYG0JZAN';
		$image_data .= '4FCkftyDT2XkQoULoojUumCGN+x3mcMzRERaSwvjDEpEYyF/LsE9VABRVmnEYtFnBkGJp5YD/7B4L58G+IQGpEkRFcd1kyx2QQURBMqA5oRP7aQEF';
		$image_data .= '1teAah0KvQ/OflhSIjmsQH6kUDicVubGiovJI9LHakYAD8UhPRVtwN0b3ZA1DYwqUjeq92AfW+WhQC8500W8YVskX8MJG/vECe25ZLB4cF5V/DeRY';
		$image_data .= 'b9IXoihDobUs3fcbZshYmGWoXsWWRhCFNDAKEkAtI8MfuTF//BVo1EVJB/EsSCOCRyPQOEgJLpAoPZ2Cf9Gj5tORJUUJQ7BIpXsSxl6bXJ6K45Cqi';
		$image_data .= 'NKkPxwNUybcOPZRGBrRctEL/5bFnR1QOG59OHJwlFbExsbGxF5cNOBplLuf0CRFBkRoskzxl2R6htLoKDTsh9ZjYII6TmBDRj9yC4eXGWbAMEXCoz';
		$image_data .= 'FiqfzpWo9+VATFWULDs9FZLgoxNz4hjULnpBM4IGXoJc3Sb5N1IBxtkaBIpXPIR4arGx9AUnA/W8zl4mGx2A2shpgZZp3vRqTPCFqVnIkmxioG73Z';
		$image_data .= 'KI9nExEQohJf6n0rnKD+CWGYoNETqH43K/BbdqH4kszW4d1rVvNRM1TCiDuWKf6TjTMUj1cdFRjU0Nv6aABbWOUqi+VFRwbOkg56k2RQ6Hf6n0xM9';
		$image_data .= 'LRUnWdSDJnNgCwW2cbY/mDe83/gNCqbAjQ8vzHyrPOzrRUwY5uMTY/zKf/MUcyXvbGxs5u/qQ2wa+62ehYZGTYfj4N8/5O3qGG1fs9NR+lBDW52QJ';
		$image_data .= 'JvYE0YZHCWRjgaN+nkIa3Rs1+b90B491BUlz2ifMweytQ7pBo3aNvDIH45Z7nyotW+n5bw7I7mv+0xiRpDHnIsPS1ihZ5nEhGm1y5aRaZhWnz0bWx';
		$image_data .= 'GPvbkaRAOMFpoRZF6RPVL6uJKPz5m68Ub+seSKzMyzVWcrvv0b0XgOJhySgler+FtAx2RUTQfY9UGq5J2tqnJ7kvc3onEKKkY185uFA4ymxS00cJs';
		$image_data .= '71BlyQ3GD5lHLrQJx9bC/DY2xoVvBiKLqOVComorP1uoNHtV/zdyw29qq1oWLnrqIk//ML4FYmlAlBw1/Bq/pYf83aExIvskV8HYPOanzOPSKWMy8';
		$image_data .= '+c3zwf5EzGcy+eL8/MzMTN7mP/PhE8c7YU4Og9ZOVXynuQTDbB79N2iY3kQ1Y7jpvsWovt4cWDsm5IuJJpSfmS/gD6osI36rbIe7mOZ0hRggIqZb8';
		$image_data .= 'eiALgyzs5Oeif3qaLCSUe9gLh5m162IBn/3wMHaTdkDS65Y9qzA9sIjrPPCxgAdUev0MSJf7JHIzqlrog76NBuRyBbrdLinc8EJs7H11XHS7BrzSO';
		$image_data .= 'Slgvm26rQCGl+dMY907DCbLk27qaLWXtEvDk6ijRicyaib7aCj0/uqaLAX8UfqHKbMoVgt/OwWk8/lcvlM3mfySZHultuYbVdvy+3pjy50e93Gpnv';
		$image_data .= '12onGT3yMYQ5dvktEd7t/0VH5bjxmc5dlM5V97182vjZdPaJ77EcQCKxHRtNnLIEz2N01YnU6TRT52hk98oUzfW/b2dkTcEHzVWWKI5Nfzdww3DzG';
		$image_data .= 'Ojcmj1k1uKjsvV+56Juvdlv9prjS1gHrWuJgC0s6t1s3YphRANaF3mnOgLvpnsGaAR/spWlzF3P6HuttgevoUvnF9m5nCyTL7VZMBMVCND1ghu10L';
		$image_data .= 'MAIwzrH3LWBL6t7vM3du+NVXhUNrPQbl7l7h933/bnJuzcNvWlQG5jupSLCWsfAktG91u/gznux1onwzm5GD3p5LLLD2EDDFtH4TgfDeo16Z/QGBP';
		$image_data .= 'iiKgo04N8Zvpivw4WAXqDh4NSJ6ihr/MaAgCWsV+c32COb7z9xJQ4tY7CJM7qxjS2aRmMwp3+pYEaIBmbUiWFTp2Je0zH2BTubTmzMVGyjCGjoPJ5';
		$image_data .= 'xG2OpEDS8JmK2/2vn0IvdbYWMwb5/1NvSjS1RMYIIUWG/Ohr/OfX+D6TEIy8dr+8AiMhhauu9li7UGuyMRKLWxzbjobHqfN/r5aQzw6kVGq1vyxgV';
		$image_data .= '2BmFwEQjzKlF1NnqcM+rS9SLdeo4XfDyHQMbN2p2iUTfs95EGk6tUrcArxD4mo8faxJfq283egesejTBf6H1jx8TTswX4cLGwJF2aB84Gt067KLZA';
		$image_data .= '8s9kmPfPBoqRhf+6pltW757fcaw/xlpTvzLX6JKN/Z3o/F3md94S+OtJPp/AQYA+87vvNe+Yy0AAAAASUVORK5CYII=';

		return $image_data;
	}

}
