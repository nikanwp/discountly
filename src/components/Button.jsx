import { Button as WPButton } from '@wordpress/components';
import clsx from 'clsx';

/**
 * @param variant
 * @param size
 * @param children
 * @param className
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
const Button = ( {
	variant = 'primary',
	size = 'default',
	children,
	className,
	...props
} ) => {
	const buttonClass = clsx(
		{
			'is-large': size === 'large',
		},
		className
	);

	return (
		<WPButton
			className={ buttonClass }
			variant={ variant }
			size={ size }
			{ ...props }
		>
			{ children }
		</WPButton>
	);
};
export default Button;
