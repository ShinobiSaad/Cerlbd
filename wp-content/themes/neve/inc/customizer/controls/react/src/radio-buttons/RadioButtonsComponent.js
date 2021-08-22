/* jshint esversion: 6 */
import PropTypes from 'prop-types'
import RadioIcons from '../common/RadioIcons'
import SVG from '../common/svg.js'
import classnames from 'classnames'

const { __ } = wp.i18n
const {
  Component
} = wp.element

class RadioButtonsComponent extends Component {
  constructor(props) {
    super( props )
    this.state = {
      value: props.control.setting.get()
    }
    this.getChoices = this.getChoices.bind( this )
    this.updateValue = this.updateValue.bind( this )
  }

  getChoices() {
    const { is_for, choices } = this.props.control.params

    if ( !is_for ) {
      return choices
    }

    if ( is_for === 'logo' ) {
      return {
        default: {
          tooltip: __( 'Logo Only', 'neve' ),
          icon: SVG.logoOnly
        },
        logoTitle: {
          tooltip: __( 'Logo - Title & Tagline', 'neve' ),
          icon: SVG.logoTitle
        },
        titleLogo: {
          tooltip: __( 'Title & Tagline - Logo', 'neve' ),
          icon: SVG.titleLogo
        },
        logoTopTitle: {
          tooltip: __( 'Logo on Top', 'neve' ),
          icon: SVG.logoTopTitle
        }
      }
    }

    if ( is_for === 'menu' ) {
      return {
        'style-plain': {
          tooltip: __( 'Plain', 'neve' ),
          icon: SVG.menuPlain
        },
        'style-full-height': {
          tooltip: __( 'Background', 'neve' ),
          icon: SVG.menuFilled
        },
        'style-border-bottom': {
          tooltip: __( 'Bottom Border', 'neve' ),
          icon: SVG.menuUnderline
        },
        'style-border-top': {
          tooltip: __( 'Top Border', 'neve' ),
          icon: SVG.menuOverline
        }
      }
    }

    if ( is_for === 'row_skin' ) {
      return {
        'light-mode': {
          tooltip: __( 'Light', 'neve' ),
          icon: SVG.light
        },
        'dark-mode': {
          tooltip: __( 'Dark', 'neve' ),
          icon: SVG.dark
        }
      }
    }

  }

  render() {
    const { label, large_buttons } = this.props.control.params
    const { value } = this.state
    const wrapClasses = classnames( [
      'neve-white-background-control',
      { 'large-buttons': large_buttons === true }] )
    return (
      <div className={wrapClasses}>
        {label && <span className='customize-control-title'>{label}</span>}
        <RadioIcons
          value={value}
          options={this.getChoices()}
          onChange={(value) => {this.updateValue( value )}}
        />
      </div>
    )
  }

  componentDidMount() {
    const { control } = this.props

    document.addEventListener( 'neve-changed-customizer-value', (e) => {
      if ( !e.detail ) return false
      if ( e.detail.id !== control.id ) return false
      this.updateValue( e.detail.value )
    } )
  }

  updateValue(newVal) {
    this.setState( { value: newVal } )
    this.props.control.setting.set( newVal )
  }
}

RadioButtonsComponent.propTypes = {
  control: PropTypes.object.isRequired
}

export default RadioButtonsComponent
