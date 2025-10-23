<?php
/**
 * Schedule management for attendants
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Schedule {

	/**
	 * Check if attendant is available based on schedule
	 *
	 * @param array $attendant Attendant data.
	 * @return bool
	 */
	public static function is_available( $attendant ) {
		// Se auto_status não está ativo, usa o status manual
		if ( empty( $attendant['auto_status'] ) || $attendant['auto_status'] !== 'yes' ) {
			return $attendant['status'] === 'online';
		}

		// Verificar se tem configuração de horário
		if ( empty( $attendant['work_start'] ) || empty( $attendant['work_end'] ) ) {
			return $attendant['status'] === 'online';
		}

		// Obter timezone
		$timezone = ! empty( $attendant['timezone'] ) ? $attendant['timezone'] : 'America/Sao_Paulo';
		
		try {
			$tz = new DateTimeZone( $timezone );
		} catch ( Exception $e ) {
			$tz = new DateTimeZone( 'America/Sao_Paulo' );
		}

		// Data/hora atual no timezone do atendente
		$now = new DateTime( 'now', $tz );
		$current_day = strtolower( $now->format( 'l' ) ); // monday, tuesday, etc
		$current_time = $now->format( 'H:i' );

		// Verificar se hoje é dia de trabalho
		$working_days = ! empty( $attendant['working_days'] ) ? $attendant['working_days'] : array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
		
		if ( ! in_array( $current_day, $working_days ) ) {
			return false; // Não trabalha hoje
		}

		// Verificar se está no horário
		$work_start = $attendant['work_start'];
		$work_end = $attendant['work_end'];

		return ( $current_time >= $work_start && $current_time <= $work_end );
	}

	/**
	 * Get current status for attendant
	 *
	 * @param array $attendant Attendant data.
	 * @return string 'online', 'away', or 'offline'
	 */
	public static function get_current_status( $attendant ) {
		// Se auto_status não está ativo, retorna status manual
		if ( empty( $attendant['auto_status'] ) || $attendant['auto_status'] !== 'yes' ) {
			return $attendant['status'];
		}

		// Verificar se está disponível baseado no horário
		return self::is_available( $attendant ) ? 'online' : 'offline';
	}

	/**
	 * Get formatted working hours
	 *
	 * @param array $attendant Attendant data.
	 * @return string
	 */
	public static function get_formatted_hours( $attendant ) {
		if ( empty( $attendant['auto_status'] ) || $attendant['auto_status'] !== 'yes' ) {
			return ! empty( $attendant['working_hours'] ) ? $attendant['working_hours'] : '';
		}

		if ( empty( $attendant['work_start'] ) || empty( $attendant['work_end'] ) ) {
			return '';
		}

		$working_days = ! empty( $attendant['working_days'] ) ? $attendant['working_days'] : array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
		
		// Nomes completos dos dias
		$days_full = array(
			'monday'    => 'Segunda',
			'tuesday'   => 'Terça',
			'wednesday' => 'Quarta',
			'thursday'  => 'Quinta',
			'friday'    => 'Sexta',
			'saturday'  => 'Sábado',
			'sunday'    => 'Domingo',
		);

		// Ordem dos dias da semana
		$days_order = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		
		// Organizar dias na ordem correta
		$sorted_days = array();
		foreach ( $days_order as $day ) {
			if ( in_array( $day, $working_days ) ) {
				$sorted_days[] = $day;
			}
		}

		$days_text = self::format_days_range( $sorted_days, $days_full );
		$hours = $attendant['work_start'] . ' às ' . $attendant['work_end'];

		return $days_text . ' - ' . $hours;
	}

	/**
	 * Format days range intelligently
	 *
	 * @param array $days Array of day keys.
	 * @param array $days_full Full day names.
	 * @return string
	 */
	private static function format_days_range( $days, $days_full ) {
		if ( empty( $days ) ) {
			return '';
		}

		// Se for todos os dias
		if ( count( $days ) === 7 ) {
			return 'Todos os dias';
		}

		// Mapeamento para índices numéricos
		$day_indexes = array(
			'monday'    => 1,
			'tuesday'   => 2,
			'wednesday' => 3,
			'thursday'  => 4,
			'friday'    => 5,
			'saturday'  => 6,
			'sunday'    => 0, // Domingo é 0 em muitos sistemas
		);

		// Reorganizar domingo no final se presente
		if ( in_array( 'sunday', $days ) ) {
			$days = array_diff( $days, array( 'sunday' ) );
			$days[] = 'sunday';
		}

		// Verificar padrões comuns
		// Segunda a Sexta
		if ( $days === array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' ) ) {
			return 'Segunda a Sexta';
		}

		// Segunda a Sábado
		if ( $days === array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) ) {
			return 'Segunda a Sábado';
		}

		// Segunda a Domingo
		if ( $days === array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ) ) {
			return 'Segunda a Domingo';
		}

		// Terça a Sábado
		if ( $days === array( 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ) ) {
			return 'Terça a Sábado';
		}

		// Quarta a Domingo
		if ( $days === array( 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ) ) {
			return 'Quarta a Domingo';
		}

		// Fim de semana
		if ( $days === array( 'saturday', 'sunday' ) ) {
			return 'Finais de Semana';
		}

		// Se for sequência consecutiva, criar intervalo
		if ( self::is_consecutive( $days ) ) {
			$first = reset( $days );
			$last = end( $days );
			return $days_full[ $first ] . ' a ' . $days_full[ $last ];
		}

		// Se não for sequência, listar todos
		$day_names = array();
		foreach ( $days as $day ) {
			if ( isset( $days_full[ $day ] ) ) {
				$day_names[] = $days_full[ $day ];
			}
		}

		// Se são apenas 2 dias, usar "e"
		if ( count( $day_names ) === 2 ) {
			return implode( ' e ', $day_names );
		}

		// Se são 3 ou mais, usar vírgula e "e" no último
		if ( count( $day_names ) > 2 ) {
			$last = array_pop( $day_names );
			return implode( ', ', $day_names ) . ' e ' . $last;
		}

		return implode( ', ', $day_names );
	}

	/**
	 * Check if days are consecutive
	 *
	 * @param array $days Array of day keys.
	 * @return bool
	 */
	private static function is_consecutive( $days ) {
		if ( count( $days ) < 2 ) {
			return false;
		}

		$day_order = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$indexes = array();
		
		foreach ( $days as $day ) {
			$index = array_search( $day, $day_order );
			if ( $index !== false ) {
				$indexes[] = $index;
			}
		}

		sort( $indexes );

		// Verificar se são consecutivos
		for ( $i = 0; $i < count( $indexes ) - 1; $i++ ) {
			if ( $indexes[ $i + 1 ] - $indexes[ $i ] !== 1 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get next available time for attendant
	 *
	 * @param array $attendant Attendant data.
	 * @return string
	 */
	public static function get_next_available( $attendant ) {
		if ( empty( $attendant['auto_status'] ) || $attendant['auto_status'] !== 'yes' ) {
			return '';
		}

		if ( self::is_available( $attendant ) ) {
			return 'Disponível agora';
		}

		// Obter timezone
		$timezone = ! empty( $attendant['timezone'] ) ? $attendant['timezone'] : 'America/Sao_Paulo';
		
		try {
			$tz = new DateTimeZone( $timezone );
		} catch ( Exception $e ) {
			$tz = new DateTimeZone( 'America/Sao_Paulo' );
		}

		$now = new DateTime( 'now', $tz );
		$current_day = strtolower( $now->format( 'l' ) );
		$current_time = $now->format( 'H:i' );

		$working_days = ! empty( $attendant['working_days'] ) ? $attendant['working_days'] : array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
		$work_start = $attendant['work_start'];

		// Se hoje é dia de trabalho e ainda não começou
		if ( in_array( $current_day, $working_days ) && $current_time < $work_start ) {
			return 'Disponível às ' . $work_start;
		}

		// Procurar próximo dia de trabalho
		$days_order = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$current_day_index = array_search( $current_day, $days_order );
		
		for ( $i = 1; $i <= 7; $i++ ) {
			$next_day_index = ( $current_day_index + $i ) % 7;
			$next_day = $days_order[ $next_day_index ];
			
			if ( in_array( $next_day, $working_days ) ) {
				$days_pt = array(
					'monday'    => 'Segunda',
					'tuesday'   => 'Terça',
					'wednesday' => 'Quarta',
					'thursday'  => 'Quinta',
					'friday'    => 'Sexta',
					'saturday'  => 'Sábado',
					'sunday'    => 'Domingo',
				);
				
				if ( $i === 1 ) {
					return 'Disponível amanhã às ' . $work_start;
				}
				
				return 'Disponível ' . $days_pt[ $next_day ] . ' às ' . $work_start;
			}
		}

		return 'Indisponível';
	}
}

