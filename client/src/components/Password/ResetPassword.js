import React, { Component } from 'react';
import {
  Container,
  Form,
  Button
} from 'react-bootstrap';

import { Link } from "react-router-dom";

import logo from '../../logo.png';
import '../../styles/components/change-password.scss';

import flashMessenger from '../../utils/flashMessenger';
import Validator from '../../utils/validator';
import api from '../../utils/api';
import config from '../../config/index';

class ResetPassword extends Component {
  constructor(props) {
    super(props);

    this.state = {
      password: ""
    };

    this.validationRules = {
      password: 'required|min:6'
    };
  }

  handleChange = (e) => {
    this.setState({ [e.target.name]: e.target.value });
  }

  resetPassword = (e) => {
    e.preventDefault();

    let validator = new Validator(this.state, this.validationRules);
    const { token } = this.props.match.params;
    const { password } = this.state;
    
    const requestData = {
      password,
      token
    };

    if (validator.isValid()) {
      api
        .put(`${config.endpoints.api}`, '/reset-password', requestData)
        .then((response) => {
          flashMessenger.show('success', 'Password Reset successfully!!');
          this.props.history.push('/');
        }).catch(error => flashMessenger.show('error', error.message));
    } else {
      flashMessenger.show('error', validator.getErrorMessages());
    }
  }

  render() {
    const { password } = this.state;

    return (
      <Container>
        <div className="change-password">
          <div className="change-password__box">
            <div className="logo__container text-center">
              <img className="logo" src={logo} alt="Logo" />
            </div>
            <Form className="change-password__form">
              <Form.Group controlId="password">
                <Form.Label>Password</Form.Label>
                <Form.Control type="password" name="password" placeholder="Password" value={password} onChange={this.handleChange} />
              </Form.Group>
              <Form.Group>
                <Button variant="primary" type="submit" block onClick={this.resetPassword}>
                  Reset Password
                </Button>
              </Form.Group>

              <Form.Group className="text-center">
                <Link to="/">&nbsp;Login</Link>
              </Form.Group>
            </Form>
          </div>
        </div>
      </Container >
    );
  }
}

export default ResetPassword;